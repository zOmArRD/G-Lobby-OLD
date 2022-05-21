<?php
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\utils;

class VISIBILITY {
    public const ALL = 0; // everyone by default.
    public const FRIENDS = 1; // TODO: Add friends system.
    public const STAFF = 2; // hide all players for the player.
    public const NOBODY = 3; // show only the staff team.
}