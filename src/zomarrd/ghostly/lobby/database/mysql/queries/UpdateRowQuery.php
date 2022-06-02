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

class UpdateRowQuery extends Query
{
    private string $updates;

    public function __construct(
        array         $updates,
        private string $conditionKey,
        private string $conditionValue,
        private string $table
    ) {
        $this->updates = serialize($updates);
        parent::__construct();
    }

    final public function query(mysqli $mysqli): void
    {
        $updates = [];
        foreach (unserialize($this->updates, [[]]) as $key => $value) {
            $updates[] = sprintf("%s='%s'", $key, $value);
        }

        $mysqli->query("UPDATE $this->table SET " . implode(',', $updates) . " WHERE $this->conditionKey='$this->conditionValue';");
    }
}