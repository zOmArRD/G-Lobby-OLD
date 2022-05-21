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

final class InsertQuery extends Query
{

    public function __construct(private string $query) { parent::__construct(); }

    public function query(mysqli $mysqli): void
    {
        $mysqli->query($this->getQuery());
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}