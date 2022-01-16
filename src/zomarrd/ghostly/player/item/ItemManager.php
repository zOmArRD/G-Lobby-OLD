<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *spo
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\player\item;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\player\IPlayer;

final class ItemManager extends IPlayer
{

	public function get(string $name): Item
	{
		return match ($name) {
			'item-servers' => VanillaItems::COMPASS()->setCustomName($this->getPlayer()->getLang()->getItemNames('item-servers')),
			'item-lobby' => VanillaItems::NETHER_STAR()->setCustomName($this->getPlayer()->getLang()->getItemNames('item-lobby')),
			'item-cosmetics' => ItemFactory::getInstance()->get(ItemIds::ENDER_CHEST)->setCustomName($this->getPlayer()->getLang()->getItemNames('item-cosmetics')),
			default => ItemFactory::air()
		};
	}
}