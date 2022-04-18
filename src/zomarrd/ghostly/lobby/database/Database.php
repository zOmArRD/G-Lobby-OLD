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

namespace zomarrd\ghostly\lobby\database;


use zomarrd\ghostly\lobby\database\mysql\MySQL;

final class Database
{
    private static MySQL $mysql;

    public function __construct() { $this->loadProviders(); }

    public function loadProviders(): void
    {
        $this->loadMySQL();
    }

    private function loadMySQL(): void
    {
        self::$mysql = new MySQL();
    }

    public static function getMysql(): MySQL
    {
        return self::$mysql;
    }
}