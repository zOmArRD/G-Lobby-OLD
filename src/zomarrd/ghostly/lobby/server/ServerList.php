<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 18/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\server;

final class ServerList
{
    public const PRACTICE = 'Practice';
    public const COMBO = 'Combo';
    public const UHC = 'UHC';
    public const UHCRUN = 'UHCRun';
    public const KITMAP = 'KitMap';
    public const HCF = 'HCF';

    public const SERVERS = [
        self::PRACTICE,
        self::COMBO,
        self::UHC,
        self::UHCRUN,
        self::KITMAP,
        self::HCF
    ];
}