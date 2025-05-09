<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\extensions\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\IPlayer;

abstract class ScoreAPI extends IPlayer
{
    public array $lines = [], $objectiveName = [];

    private SetDisplayObjectivePacket $displayPacket;

    public function __construct(GhostlyPlayer $player)
    {
        $this->displayPacket = new SetDisplayObjectivePacket();
        parent::__construct($player);
    }

    final public function removeObjectiveName(): void
    {
        unset($this->objectiveName[$this->getPlayerName()]);
    }

    final public function new(string $objectiveName, string $displayName): void
    {
        if ($this->isObjectiveName()) {
            $this->remove();
        }

        $this->getDisplayPacket()->objectiveName = $objectiveName;
        $this->getDisplayPacket()->displayName = $displayName;
        $this->getDisplayPacket()->sortOrder = 0;
        $this->getDisplayPacket()->displaySlot = 'sidebar';
        $this->getDisplayPacket()->criteriaName = 'dummy';
        $this->setObjectiveName($objectiveName);
        $this->getPlayer()->getNetworkSession()->sendDataPacket($this->getDisplayPacket());
    }

    final public function isObjectiveName(): bool
    {
        return isset($this->objectiveName[$this->getPlayer()->getName()]);
    }

    final public function remove(): void
    {
        $packet = new RemoveObjectivePacket();
        $packet->objectiveName = $this->getObjectiveName();
        $this->getPlayer()->getNetworkSession()->sendDataPacket($packet);
    }

    final public function getObjectiveName(): string
    {
        return $this->objectiveName[$this->getPlayerName()];
    }

    final public function getDisplayPacket(): SetDisplayObjectivePacket
    {
        return $this->displayPacket;
    }

    final public function setObjectiveName(string $objectiveName): void
    {
        $this->objectiveName[$this->getPlayerName()] = $objectiveName;
    }

    final public function setLine(int $score, string $message): void
    {
        if ($this->isObjectiveName()) {
            if ($score > 15 || $score < 0) {
                Ghostly::$logger->error("Score must be between the value of 0-15. $score out of range.");
                return;
            }

            $entry = new ScorePacketEntry();
            $entry->objectiveName = $this->getObjectiveName();
            $entry->type = $entry::TYPE_FAKE_PLAYER;

            if (isset($this->lines[$score])) {
                $packet1 = new SetScorePacket();
                $packet1->entries[] = $this->lines[$score];
                $packet1->type = $packet1::TYPE_REMOVE;
                $this->getPlayer()->getNetworkSession()->sendDataPacket($packet1);
                unset($this->lines[$score]);
            }

            $entry->score = $score;
            $entry->scoreboardId = $score;
            $entry->customName = $message;
            $this->lines[$score] = $entry;

            $packet2 = new SetScorePacket();
            $packet2->entries[] = $entry;
            $packet2->type = $packet2::TYPE_CHANGE;
            $this->getPlayer()->getNetworkSession()->sendDataPacket($packet2);
        }
    }
}