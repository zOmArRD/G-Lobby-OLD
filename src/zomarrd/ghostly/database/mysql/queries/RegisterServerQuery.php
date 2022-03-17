<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\database\mysql\queries;

use mysqli;
use zomarrd\ghostly\database\mysql\MySQL;
use zomarrd\ghostly\database\mysql\Query;
use zomarrd\ghostly\Ghostly;

final class RegisterServerQuery extends Query
{
    public function __construct(private string $serverName) {}

    public function query(mysqli $mysqli): void
    {
        $result = $mysqli->query("SELECT * FROM ghostly_servers WHERE server_name = '$this->serverName';");
        if ($result !== false) {
            $assoc = $result->fetch_assoc();
            if (is_array($assoc)) {
                $mysqli->query("UPDATE ghostly_servers SET online = 1 WHERE server_name = '$this->serverName';");
            } else {
                $category = Ghostly::CATEGORY;

                $mysqli->query("INSERT INTO ghostly_servers(server_name, players, max_players, online, whitelist, category) VALUES ('$this->serverName', 0, 0, true, true, '$category');");
            }
        } else {
            MySQL::runAsync(new self($this->serverName));
        }
    }
}