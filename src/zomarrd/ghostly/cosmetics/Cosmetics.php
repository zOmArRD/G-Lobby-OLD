<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/3/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\cosmetics;

use zomarrd\ghostly\lobby\Ghostly;

final class Cosmetics
{
    public function __construct(private Ghostly $ghostly) {}

    public function getGhostly(): Ghostly
    {
        return $this->ghostly;
    }

    public function load(): void
    {
        $this->ghostly->registerEvents([]);
    }
}