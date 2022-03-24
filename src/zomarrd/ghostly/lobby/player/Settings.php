<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 24/3/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\player;

final class Settings
{
    public function __construct(private GhostlyPlayer $player) {}

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }

}