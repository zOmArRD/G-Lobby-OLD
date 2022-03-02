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
use pocketmine\nbt\tag\CompoundTag;
use zomarrd\ghostly\player\IPlayer;

final class ItemManager extends IPlayer
{
    /**
     * @param string $name
     *
     * @return Item
     * @todo make this better?
     */
    public function get(string $name): Item
    {
        return match ($name) {
            'item-servers' => VanillaItems::COMPASS()->setNamedTag((new CompoundTag())->setString("itemId", "item-servers"))->setCustomName($this->getPlayer()->getLang()->getItemNames('item-servers')),
            'item-lobby' => VanillaItems::NETHER_STAR()->setNamedTag((new CompoundTag())->setString("itemId", "item-lobby"))->setCustomName($this->getPlayer()->getLang()->getItemNames('item-lobby')),
            'item-cosmetics' => ItemFactory::getInstance()->get(ItemIds::ENDER_CHEST)->setNamedTag((new CompoundTag())->setString("itemId", "item-cosmetics"))->setCustomName($this->getPlayer()->getLang()->getItemNames('item-cosmetics')),
            'item-config' => VanillaItems::SKELETON_SKULL()->setNamedTag((new CompoundTag())->setString("itemId", "item-config"))->setCustomName($this->getPlayer()->getLang()->getItemNames('item-config')),
            default => ItemFactory::air()
        };
    }
}