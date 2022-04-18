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
use zomarrd\ghostly\database\mysql\Query;

class UpdateRowQuery extends Query
{
    public function __construct(
        private string $updates,
        private string $conditionKey,
        private string $conditionValue,
        private string $table
    )
    {
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