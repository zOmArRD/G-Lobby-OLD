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
    public const CREATE_SERVERS_TABLE = 'CREATE TABLE IF NOT EXISTS `ghostly_servers` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(26) NOT NULL,
        `ip` VARCHAR(50) NOT NULL DEFAULT \'play.ghostlymc.live\',
        `port` INT(5) NOT NULL DEFAULT 19132,
        `online` BOOLEAN NOT NULL DEFAULT TRUE,
        `maxplayers` INT NOT NULL DEFAULT 0,
        `onlineplayers` INT NOT NULL DEFAULT 0,
        `whitelisted` BOOLEAN NOT NULL DEFAULT TRUE,
        `category` VARCHAR(30) NOT NULL DEFAULT \'Lobby\',
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    );';

    public const CREATE_PLAYER_DATA_TABLE = 'CREATE TABLE IF NOT EXISTS `ghostly_playerdata` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `xuid` VARCHAR(50) NOT NULL,
        `username` VARCHAR(50) NOT NULL,
        `scoreboard` BOOLEAN DEFAULT TRUE,
        `language` VARCHAR(16) DEFAULT \'en_EN\',
        `ip` VARCHAR(50) DEFAULT \'\',
        `city` VARCHAR(50) DEFAULT \'\',
        `region` VARCHAR(50) DEFAULT \'\',
        `country` VARCHAR(50) DEFAULT \'\',
        `continent` VARCHAR(50) DEFAULT \'\',
        PRIMARY KEY (`id`),
        UNIQUE KEY `xuid` (`xuid`)
    );';

    public const CREATE_DEFAULT_TABLES = [
        self::CREATE_SERVERS_TABLE,
        self::CREATE_PLAYER_DATA_TABLE,
    ];

    public const CREATE_DEFAULT_DB = 'CREATE DATABASE IF NOT EXISTS' . MySQL['prefix'] . '`ghostly`;';

    /**
     * TODO: Refactored to better handle the SQL strings.
     */
    public const INSERT_INTO_SERVERS = "INSERT INTO ghostly_servers (name, ip, port, online, maxplayers, onlineplayers, whitelisted, category) VALUES ('%s', '%s', %s, %s, %s, %s, %s, '%s');";
}

