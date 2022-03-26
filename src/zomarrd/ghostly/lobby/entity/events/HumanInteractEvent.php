<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 6/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\entity\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use zomarrd\ghostly\lobby\entity\type\HumanType;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;

class HumanInteractEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(private HumanType $entity, private GhostlyPlayer $player) {}

    final public function getEntity(): HumanType
    {
        return $this->entity;
    }

    final public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }
}