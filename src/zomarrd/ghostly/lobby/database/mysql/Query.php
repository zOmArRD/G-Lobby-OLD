<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 24/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\database\mysql;

use mysqli;
use pocketmine\scheduler\AsyncTask;

abstract class Query extends AsyncTask
{
    public string $host, $user, $password, $database;

    final public function onRun(): void
    {
        $this->query($mysqli = new mysqli($this->host, $this->user, $this->password, $this->database));

        if ($mysqli->connect_errno) {
            die(PREFIX . 'Could not connect to the database!');
        }

        $mysqli->close();
    }

    abstract public function query(mysqli $mysqli): void;

    public function onCompletion(): void
    {
        MySQL::submitAsync($this);
    }

    final public function setHost(string $host): Query
    {
        $this->host = $host;
        return $this;
    }

    final public function setUser(string $user): Query
    {
        $this->user = $user;
        return $this;
    }

    final public function setPassword(string $password): Query
    {
        $this->password = $password;
        return $this;
    }

    final public function setDatabase(string $database): Query
    {
        $this->database = $database;
        return $this;
    }
}