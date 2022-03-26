<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/3/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */

namespace zomarrd\ghostly\lobby\player;

final class Cooldown
{

    public int|float $cooldown = 0;

    public function hasCooldown(float|int $time): bool
    {
        return time() - $this->cooldown < $time;
    }

    public function setCooldown(): void
    {
        $this->cooldown = time();
    }
}