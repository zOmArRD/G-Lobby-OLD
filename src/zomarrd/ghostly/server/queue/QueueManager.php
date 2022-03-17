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

    private array $queue;

    public function enable(Ghostly $ghostly): void
    {
        foreach ([Server::HCF, Server::COMBO, Server::PRACTICE, Server::KITMAP, Server::UHC, Server::UHC_RUN] as $server) {
            $this->queue[$server] = [];
        }

        $ghostly->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () {

                $this->updateQueue(Server::HCF);
                $this->updateQueue(Server::COMBO);
                $this->updateQueue(Server::PRACTICE);
                $this->updateQueue(Server::KITMAP);
                $this->updateQueue(Server::UHC);
                $this->updateQueue(Server::UHC_RUN);

        }), 60);
    }

    /**
     * It is responsible for updating the Queue's
     */
    public function updateQueue(string $server): void
    {
        /** @var Queue $queue */
        foreach ($this->queue[$server] as $key => $queue) {
            if ($queue->getPlayer()->isOnline()) {
                $queue->setPosition($key + 1);
                $queue->setPositionFormatted("§f" . $queue->getPosition() . "§7/§f" . count($this->queue[$server]));
                # Not Necessary !?
                //$queue->getPlayer()->sendTranslated(LangKey::QUEUE_PLAYER_NOTICE, ["{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
            }

            if (($key !== 0) && !$queue->getPlayer()->isOnline()) {
                continue;
            }

            //$this->remove($queue->getPlayer(), $queue->getServer());
            //$queue->getPlayer()->transferTo($queue->getServer());
        }
    }

    /**
     * @param GhostlyPlayer $player Player {@link GhostlyPlayer}.
     * @param string|Server $server Server {@link Server}.
     * Add the player to the Queue.
     */
    public function add(GhostlyPlayer $player, string|Server $server): void
    {
        if ($player->hasPermission(PermissionKey::GHOSTLY_SERVER_JOIN_BYPASS)) {
            $player->transferTo($server);
            return;
        }

        if ($this->exist($player)) {
            $player->sendTranslated(LangKey::QUEUE_PLAYER_EXIST, ["{SERVER-NAME}" => $player->getQueue()?->getServer()]);
            return;
        }

        $queue = $player->setQueue($server);
        $this->queue[$queue->getServer()][] = $queue;

        $player->getQueueItem();

        $position = array_search($player->getQueue(), $this->queue[$queue->getServer()], true);

        $queue->setPosition((int)$position + 1);
        $queue->setPositionFormatted("§f" . $queue->getPosition() . "§7/§f" . count($this->queue[$queue->getServer()]));

        $player->sendTranslated(LangKey::QUEUE_PLAYER_ADDED, ["{SERVER-NAME}" => $server, "{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
    }

    /**
     * Check if the player is already in a Queue.
     *
     * @param GhostlyPlayer $player
     *
     * @return bool is in Queue.
     */
    public function exist(GhostlyPlayer $player): bool
    {
        return $player->getQueue() !== null;
    }

    /**
     *  Remove player from Queue.
     */
    public function remove(GhostlyPlayer $player, string $server): void
    {
        if (!$this->exist($player)){
            return;
        }

        /** @var Queue $queue */
        foreach ($this->queue[$server] as $key => $queue) {
            if ($queue->getPlayer() === $player) {
                unset($this->queue[$server][$key]);
                $this->queue[$server] = array_values($this->queue[$server]);
                $player->quitQueue();
            }
        }
    }
}