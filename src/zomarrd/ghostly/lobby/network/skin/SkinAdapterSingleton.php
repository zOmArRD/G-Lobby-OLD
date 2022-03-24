<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\network\skin;

class SkinAdapterSingleton
{
    private static ?MojangAdapter $mojangAdapter;

    public static function get(): MojangAdapter
    {
        if (!isset(self::$mojangAdapter)) {
            self::$mojangAdapter = new MojangAdapter();
        }

        return self::$mojangAdapter;
    }
}