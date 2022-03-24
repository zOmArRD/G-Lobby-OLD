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
use zomarrd\ghostly\lobby\database\mysql\Query;

class SelectQuery extends Query
{
    public mixed $rows;

    public function __construct(private string $query) {}

    public function query(mysqli $mysqli): void
    {
        $result = $mysqli->query($this->query);
        $rows = [];

        if ($result === false) {
            return;
        }

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $this->rows = serialize($rows);
    }

    public function onCompletion(): void
    {
        if ($this->rows === null) {
            return;
        }

        $this->rows = unserialize($this->rows, [[]]);
        parent::onCompletion();
    }
}