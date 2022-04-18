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

namespace zomarrd\ghostly\lobby\server\queue;

use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\player\permission\PermissionKey;
use zomarrd\ghostly\lobby\server\Server;
use zomarrd\ghostly\lobby\server\ServerList;
use zomarrd\ghostly\lobby\server\ServerManager;

final class QueueManager
{
    use SingletonTrait;

    private array $queue;

    public function enable(Ghostly $ghostly): void
    {
        foreach (ServerList::SERVERS as $server) {
            $this->queue[$server] = [];
        }

        $ghostly->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() {

            $this->updateQueue(ServerList::HCF);
            $this->updateQueue(ServerList::COMBO);
            $this->updateQueue(ServerList::PRACTICE);
            $this->updateQueue(ServerList::KITMAP);
            $this->updateQueue(ServerList::UHC);
            $this->updateQueue(ServerList::UHCRUN);

        }), 60);
    }

    /**
     * It is responsible for updating the Queue's
     */
    public function updateQueue(string $serverName): void
    {
        foreach ($this->queue[$serverName] as $key => $queue) {
            assert($queue instanceof Queue);
            if ($queue->getPlayer()->isOnline()) {
                $queue->setPosition($key + 1);
                $queue->setPositionFormatted(sprintf("§f%s§7/§f%s", $queue->getPosition(), count($this->queue[$serverName])));
            }

            if (($key !== 0) && !$queue->getPlayer()->isOnline()) {
                continue;
            }

            $server = ServerManager::getInstance()->getServerByName($queue->getServer());

            if ($server?->isOnline()) {
                $this->remove($queue->getPlayer(), $queue->getServer());
                $queue->getPlayer()->transferTo($queue->getServer());
                return;
            }

            if (!$queue->getPlayer()->getMessageReceivedDelay(10)) {
                $queue->getPlayer()->setMessageReceivedDelay();
                $queue->getPlayer()->sendTranslated(LangKey::QUEUE_SERVER_OFFLINE, ["{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
            }
        }
    }

    /**
     *  Remove player from Queue.
     */
    public function remove(GhostlyPlayer $player, string $server): void
    {
        if (!$this->exist($player)) {
            return;
        }

        foreach ($this->queue[$server] as $key => $queue) {
            assert($queue instanceof Queue);
            if ($queue->getPlayer() === $player) {
                unset($this->queue[$server][$key]);
                $this->queue[$server] = array_values($this->queue[$server]);
                $player->quitQueue();
            }
        }
    }

    /**
     * Check if the player is already in a Queue.
     */
    public function exist(GhostlyPlayer $player): bool
    {
        return $player->getQueue() !== null;
    }

    /**
     * Add the {@link GhostlyPlayer} to the Queue.
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

        if (ServerManager::getInstance()->getServerByName($server) === null) {
            $player->sendSound(LevelSoundEvent::EXPLODE);
            $player->sendTranslated(LangKey::SERVER_CONNECT_ERROR_3);
            return;
        }

        $queue = $player->setQueue($server);
        $this->queue[$queue->getServer()][] = $queue;

        $player->getQueueItem();

        $position = array_search($player->getQueue(), $this->queue[$queue->getServer()], true);

        $queue->setPosition((int)$position + 1);
        $queue->setPositionFormatted(sprintf("§f%s§7/§f%s", $queue->getPosition(), count($this->queue[$queue->getServer()])));

        $player->sendTranslated(LangKey::QUEUE_PLAYER_ADDED, [
            "{SERVER-NAME}" => $server,
            "{POSITION-QUEUE}" => $queue->getPositionFormatted()
        ]);
    }
}