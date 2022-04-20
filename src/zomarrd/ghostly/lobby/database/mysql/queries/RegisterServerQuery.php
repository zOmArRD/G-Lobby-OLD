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

namespace zomarrd\ghostly\lobby\database\mysql\queries;

use mysqli;
use zomarrd\ghostly\lobby\database\mysql\SQLStrings;
use zomarrd\ghostly\lobby\Ghostly;

final class RegisterServerQuery
{
    public function __construct(string $name) { $this->registerServer($name); }

    public function registerServer(string $name): void
    {
        $mysqli = new mysqli(MySQL['host'], MySQL['user'], MySQL['pass'], MySQL['db'], MySQL['port']);
        if ($mysqli->connect_error) {
            die(PREFIX . 'Could not connect to the database!');
        }

        $result = $mysqli->query("SELECT * FROM servers WHERE name = '$name';");
        if ($result !== false) {
            $assoc = $result->fetch_assoc();
            if (is_array($assoc)) {
                $mysqli->query("UPDATE servers SET online = 1 WHERE name = '$name';");
            } else {
                $mysqli->query(sprintf(SQLStrings::INSERT_INTO_SERVERS, $name, Server['ip'], Server['port'], 1, Ghostly::getInstance()->getServer()->getMaxPlayers(), 0, 1, Server['category']));
            }
        } else {
            $mysqli->close();
            new self($name);
            return;
        }

        $mysqli->close();
        Ghostly::$logger->info(PREFIX . 'Registering the server in the database - Successful');
    }
}