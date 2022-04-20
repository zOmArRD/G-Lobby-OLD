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

use zomarrd\ghostly\database\mysql\MySQL;

final class MySQLProvider
{
    public static function createTables(): void
    {
        MySQL::getInstance()->run(SQLStrings::CREATE_DEFAULT_DB);

        foreach (SQLStrings::CREATE_DEFAULT_TABLES as $query) {
            MySQL::getInstance()->run($query);
        }
    }
}