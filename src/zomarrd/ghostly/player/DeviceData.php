<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 4/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\player;

final class DeviceData extends IPlayer
{
    private static array $UIProfiles;

    public static function saveUIProfile(string $player_name, int $profile): void
    {
        self::$UIProfiles[$player_name] = $profile;
    }

    public static function getUIProfile(string $player_name): int
    {
        return self::$UIProfiles[$player_name];
    }
}