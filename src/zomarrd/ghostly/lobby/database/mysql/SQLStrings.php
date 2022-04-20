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

    public const CREATE_TABLE_SERVERS = 'CREATE TABLE IF NOT EXISTS `servers` (
        name VARCHAR(26) UNIQUE,
        ip VARCHAR(50),
        port INT(5) UNIQUE,
        online BOOLEAN,
        maxplayers INT,
        onlineplayers INT,
        whitelisted BOOLEAN,
        category VARCHAR(30)
    );';

    public const CREATE_PLAYER_DATA = "CREATE TABLE IF NOT EXISTS `player_data` (
        xuid VARCHAR(50) UNIQUE,
        username VARCHAR(50) UNIQUE,
        scoreboard BOOLEAN DEFAULT TRUE,
        language VARCHAR(16),
        ip VARCHAR(50) DEFAULT '',
        city VARCHAR(50) DEFAULT '',
        region VARCHAR(50) DEFAULT '',
        country VARCHAR(50) DEFAULT '',
        continent VARCHAR(50) DEFAULT ''
);";

    public const CREATE_DEFAULT_TABLES = [
        self::CREATE_TABLE_SERVERS,
        self::CREATE_PLAYER_DATA,
    ];

    public const CREATE_DEFAULT_DB = 'CREATE DATABASE IF NOT EXISTS' . MySQL['prefix'] . '`ghostly`;';

    public const INSERT_INTO_SERVERS = "INSERT INTO servers (name, ip, port, online, maxplayers, onlineplayers, whitelisted, category) VALUES ('%s', '%s', %s, %s, %s, %s, %s, '%s');";
}

