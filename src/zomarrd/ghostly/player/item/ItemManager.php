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
use pocketmine\item\ItemIds;
use zomarrd\ghostly\player\IPlayer;

final class ItemManager extends IPlayer
{
	public function get(string $name): Item
	{
		return match ($name) {
			'server-selector' => $this->loadItem(ItemIds::COMPASS, $this->getPlayer()->getLang()->getItemNames('server-selector')),
			default => ItemFactory::air()
		};
	}

	/**
	 * @param int         $item
	 * @param string|null $customName
	 *
	 * @return Item
	 */
	public function loadItem(int $item, ?string $customName): Item
	{
		return $customName !== null ? ItemFactory::getInstance()->get($item)->setCustomName($customName) : ItemFactory::getInstance()->get($item);
	}
}