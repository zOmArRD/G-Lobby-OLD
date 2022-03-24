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

namespace zomarrd\ghostly\lobby\player\item;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use zomarrd\ghostly\lobby\player\IPlayer;

final class ItemManager extends IPlayer
{
    public const SERVER_SELECTOR = 'server-selector';
    public const COSMETICS_SELECTOR = 'cosmetics-selector';
    public const LOBBY_SELECTOR = 'lobby-selector';
    public const VISIBILITY_ALL = 'visibility-all';
    public const VISIBILITY_STAFF = 'visibility-staff';
    public const VISIBILITY_NOBODY = 'visibility-nobody';
    public const PERSONAL_SETTINGS = 'personal-settings';
    public const QUEUE_EXIT = 'queue-exit';

    public function get(string $name): Item
    {
        return match ($name) {
            self::SERVER_SELECTOR => $this->getFormatted($name, VanillaItems::COMPASS()),
            self::COSMETICS_SELECTOR => $this->getFormatted($name, ItemFactory::getInstance()->get(ItemIds::ENDER_CHEST)),
            self::VISIBILITY_ALL => $this->getFormatted($name, VanillaItems::LIME_DYE()),
            self::LOBBY_SELECTOR => $this->getFormatted($name, VanillaItems::NETHER_STAR()),
            self::PERSONAL_SETTINGS => $this->getFormatted($name, ItemFactory::getInstance()->get(ItemIds::COMPARATOR)),
            self::QUEUE_EXIT => $this->getFormatted($name, VanillaItems::REDSTONE_DUST()),
            default => ItemFactory::air()
        };
    }

    public function getFormatted(string $id, Item $item): Item
    {
        return $item->setCustomName($this->getPlayer()->getLang()->getItemNames($id))->setNamedTag((new CompoundTag())->setString('ItemID', $id));
    }
}