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

use GhostlyMC\DatabaseAPI\mysql\Query;
use mysqli;

class SelectQuery extends Query
{
    public mixed $rows;

    public function __construct(private string $table, private ?string $conditionKey = null, private ?string $key = null) { parent::__construct(); }

    final public function query(mysqli $mysqli): void
    {
        if (!isset($this->conditionKey)) {
            $result = $mysqli->query(sprintf("SELECT * FROM %s", $this->table));
        } else {
            $result = $mysqli->query(sprintf("SELECT * FROM %s WHERE %s = '%s'", $this->table, $this->conditionKey, $this->key));
        }

        $rows = [];

        if ($result === false) {
            return;
        }

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $this->rows = serialize($rows);
    }

    final public function onCompletion(): void
    {
        if ($this->rows === null) {
            return;
        }

        $this->rows = unserialize($this->rows, [[]]);
        parent::onCompletion();
    }
}