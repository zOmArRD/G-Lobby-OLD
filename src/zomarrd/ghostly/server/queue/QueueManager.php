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

    private array $queue = [];

    /** @var array<Queue> */
    private array $practice, $combo = [], $hcf = [], $kitmap = [], $uhc = [], $uhcrun = [];

    public function enable(Ghostly $ghostly): void
    {
        $ghostly->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($ghostly) {
            $this->updateQueue();
        }), 60);
    }

    /**
     * It is responsible for updating the Queue's
     */
    public function updateQueue(): void
    {
        /**
         * @var int   $key
         * @var Queue $queue
         */
        foreach ($this->queue as $key => $queue) {
            if ($queue->getPlayer()->isOnline()) {
                $queue->setPosition($key + 1);
                $queue->setPositionFormatted("§f" . $queue->getPosition() . "§7/§f" . count($this->queue));
                $queue->getPlayer()->sendTranslated(LangKey::QUEUE_PLAYER_NOTICE, ["{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
            }

            if (($key !== 0) && !$queue->getPlayer()->isOnline()) {
                continue;
            }

            $this->remove($queue->getPlayer());
            $queue->getPlayer()->transferTo($queue->getServer());
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
        $this->queue[] = $queue;

        $player->getQueueItem();

        $position = array_search($player->getQueue(), $this->queue, true);

        $queue->setPosition((int)$position + 1);
        $queue->setPositionFormatted("§f" . $queue->getPosition() . "§7/§f" . count($this->queue));

        $player->sendTranslated(LangKey::QUEUE_PLAYER_ADDED, ["{SERVER-NAME}" => $server, "{POSITION-QUEUE}" => $queue->getPositionFormatted()]);
    }

    /**
     * Check if the player is already in a Queue.
     *
     * @param GhostlyPlayer $player
     * @param string        $server
     *
     * @return bool is in Queue.
     */
    public function exist(GhostlyPlayer $player, string $server): bool
    {
        switch ($server) {
            case Server::PRACTICE:
                foreach ($this->practice as $queue) {
                    if ($queue->getPlayer() !== $player) {
                        continue;
                    }

                    return true;
                }
                break;
            case Server::COMBO:
                foreach ($this->combo as $queue) {
                    if ($queue->getPlayer() !== $player) {
                        continue;
                    }

                    return true;
                }
                break;
            case Server::UHC:
                foreach ($this->uhc as $queue) {
                    if ($queue->getPlayer() !== $player) {
                        continue;
                    }

                    return true;
                }
                break;
            case Server::UHC_RUN:
                foreach ($this->uhcrun as $queue) {
                    if ($queue->getPlayer() !== $player) {
                        continue;
                    }

                    return true;
                }
                break;
            case Server::HCF:
                foreach ($this->hcf as $queue) {
                    if ($queue->getPlayer() !== $player) {
                        continue;
                    }

                    return true;
                }
                break;
            case Server::KITMAP:
                foreach ($this->kitmap as $queue) {
                    if ($queue->getPlayer() !== $player) {
                        continue;
                    }

                    return true;
                }
                break;
            default:
                return false;
        }

        foreach ($this->queue[$server] as $queue) {
            if ($queue->getPlayer() !== $player) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     *  Remove player from Queue.
     */
    public function remove(GhostlyPlayer $player): void
    {
        foreach ($this->queue as $key => $value) {
            if ($value->getPlayer() === $player) {
                unset($this->queue[$key]);
                $this->queue = array_values($this->queue);
                $player->quitQueue();
            }
        }
    }
}