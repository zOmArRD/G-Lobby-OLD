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

final class MySQL extends \zomarrd\ghostly\database\mysql\MySQL
{
    public function createTables(): void
    {
        foreach ([SQLStrings::CREATE_TABLE_SERVERS] as $query) {
            $this->run($query);
        }
    }
}