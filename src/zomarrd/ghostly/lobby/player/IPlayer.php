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

namespace zomarrd\ghostly\lobby\player;

abstract class IPlayer
{
    public function __construct(private GhostlyPlayer $player) {}

    public function getPlayerName(): string
    {
        return $this->getPlayer()->getName();
    }

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }
}