<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 4/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\utils\menu;

use Closure;
use pocketmine\item\Item;
use zomarrd\ghostly\player\GhostlyPlayer;

class MenuButton
{
	public function __construct(
		private Item     $item,
		private ?Closure $closure = null
	){}

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