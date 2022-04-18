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
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(26) NOT NULL UNIQUE,
        ip VARCHAR(50) NOT NULL,
        port INT(5) NOT NULL UNIQUE,
        online BOOLEAN DEFAULT FALSE,
        maxplayers INT DEFAULT 0,
        onlineplayers INT DEFAULT 0,
        whitelisted BOOLEAN DEFAULT FALSE,
        category VARCHAR(26) NOT NULL,
        PRIMARY KEY (id)
    )";
}

