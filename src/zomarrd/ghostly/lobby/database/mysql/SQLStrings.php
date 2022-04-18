<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 17/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\database\mysql;

final class SQLStrings
{

    public const CREATE_TABLE_SERVERS = "CREATE TABLE IF NOT EXISTS `servers` (
        name VARCHAR(26) UNIQUE,
        ip VARCHAR(50),
        port INT(5) UNIQUE,
        online BOOLEAN,
        maxplayers INT,
        onlineplayers INT,
        whitelisted BOOLEAN,
        category VARCHAR(30)
    )";
}

