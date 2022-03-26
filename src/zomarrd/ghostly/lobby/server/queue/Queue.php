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

use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\server\Server;

final class Queue
{
    public int $position = 0;
    public string $positionFormatted = "";

    public function __construct(private GhostlyPlayer $player, private string|Server $server) {}

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }

    public function getServer(): string
    {
        if ($this->server instanceof Server) {
            return $this->server->getName();
        }
        return $this->server;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPositionFormatted(): string
    {
        return $this->positionFormatted;
    }

    public function setPositionFormatted(string $positionFormatted): void
    {
        $this->positionFormatted = $positionFormatted;
    }
}