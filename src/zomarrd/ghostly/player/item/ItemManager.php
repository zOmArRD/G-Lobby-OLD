<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\player\item;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\player\IPlayer;

final class ItemManager extends IPlayer
{

	public function get(string $name): Item
	{
		return match ($name) {
			'server-selector' => VanillaItems::COMPASS()->setCustomName($this->getPlayer()->getLang()->getItemNames('server-selector')),
			'lobby-selector' => VanillaItems::NETHER_STAR()->setCustomName($this->getPlayer()->getLang()->getItemNames('lobby-selector')),
			default => ItemFactory::air()
		};
	}
}