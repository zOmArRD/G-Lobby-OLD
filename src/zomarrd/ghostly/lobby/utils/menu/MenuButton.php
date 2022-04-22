<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 22/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\utils\menu;

use Closure;
use pocketmine\item\Item;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;

final class MenuButton
{
    public function __construct(private Item $item, private ?Closure $closure = null) {}

    public function getItem(): Item
    {
        return $this->item;
    }

    public function click(GhostlyPlayer $player): void
    {
        if ($this->closure !== null) {
            ($this->closure)($player);
        }
    }
}