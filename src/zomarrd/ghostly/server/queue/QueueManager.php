<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 2/3/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\server\queue;

use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\player\permission\PermissionKey;
use zomarrd\ghostly\server\Server;

class QueueManager
{
    use SingletonTrait;

    private array $queueList = [];

    public function enable(Ghostly $ghostly): void
    {
        $ghostly->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($ghostly) {
            $this->updateQueue();
        }), 60);
    }

    public function updateQueue(): void
    {
        /**
         * @var int   $key
         * @var Queue $queue
         */
        foreach ($this->queueList as $key => $queue) {
            if ($queue->getPlayer()->isOnline()) {
                $queue->setPosition($key + 1);
                $queue->setPositionFormatted("§f" . $queue->getPosition(). "§7/§f" . count($this->queueList));
                $queue->getPlayer()->sendTranslated(LangKey::QUEUE_PLAYER_NOTICE, ["{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
            }

            if (($key !== 0) && !$queue->getPlayer()->isOnline()) {
                continue;
            }

            $this->remove($queue->getPlayer());
            $queue->getPlayer()->transferTo($queue->getServer());
        }
    }

    public function add(GhostlyPlayer $player, string|Server $server): void
    {
        if ($player->hasPermission(PermissionKey::GHOSTLY_SERVER_JOIN_BYPASS)) {
            $player->transferTo($server);
            return;
        }

        if ($this->exist($player)) {
            $player->sendTranslated(LangKey::QUEUE_PLAYER_EXIST, ["{SERVER-NAME}" => $server]);
            return;
        }

        $queue = $player->setQueue($server);
        $this->queueList[] = $queue;

        $player->getQueueItem();

        $position = array_search($player->getQueue(), $this->queueList, true);

        $queue->setPosition((int)$position + 1);
        $queue->setPositionFormatted("§f" . $queue->getPosition(). "§7/§f" . count($this->queueList));

        $player->sendTranslated(LangKey::QUEUE_PLAYER_ADDED, ["{SERVER-NAME}" => $server, "{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
    }

    public function exist(GhostlyPlayer $player): bool
    {
        foreach ($this->queueList as $value) {
            if ($value->getPlayer() !== $player) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function remove(GhostlyPlayer $player): void
    {
        foreach ($this->queueList as $key => $value) {
            if ($value->getPlayer() === $player) {
                unset($this->queueList[$key]);
                $this->queueList = array_values($this->queueList);
                $player->quitQueue();
            }
        }
    }
}