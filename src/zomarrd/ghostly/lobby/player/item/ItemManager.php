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

use JetBrains\PhpStorm\ExpectedValues;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use zomarrd\ghostly\lobby\player\IPlayer;
use zomarrd\ghostly\lobby\player\language\LangKey;

final class ItemManager extends IPlayer
{
    public const ALL_ITEMS = [
        LangKey::ITEM_COSMETICS_SELECTOR,
        LangKey::ITEM_SERVER_SELECTOR,
        LangKey::ITEM_LOBBY_SELECTOR,
        LangKey::ITEM_VISIBILITY_ALL,
        LangKey::ITEM_VISIBILITY_STAFF,
        LangKey::ITEM_VISIBILITY_NONE,
        LangKey::ITEM_VISIBILITY_FRIENDS,
        LangKey::ITEM_QUEUE_EXIT,
        LangKey::ITEM_PLAYER_SETTINGS
    ];

    /** @noinspection PhpDeprecationInspection */
    public function get(#[ExpectedValues(self::ALL_ITEMS)] string $name): Item
    {
        return match ($name) {
            LangKey::ITEM_SERVER_SELECTOR => $this->getFormatted($name, VanillaItems::COMPASS()),
            LangKey::ITEM_COSMETICS_SELECTOR => $this->getFormatted($name, ItemFactory::getInstance()->get(ItemIds::ENDER_CHEST)),
            LangKey::ITEM_VISIBILITY_ALL => $this->getFormatted($name, VanillaItems::LIME_DYE()),
            LangKey::ITEM_VISIBILITY_STAFF => $this->getFormatted($name, VanillaItems::PINK_DYE()),
            LangKey::ITEM_VISIBILITY_NONE => $this->getFormatted($name, VanillaItems::GRAY_DYE()),
            LangKey::ITEM_LOBBY_SELECTOR => $this->getFormatted($name, VanillaItems::NETHER_STAR()),
            LangKey::ITEM_PLAYER_SETTINGS => $this->getFormatted($name, ItemFactory::getInstance()->get(ItemIds::COMPARATOR)),
            LangKey::ITEM_QUEUE_EXIT => $this->getFormatted($name, VanillaItems::REDSTONE_DUST()),
            default => ItemFactory::air()
        };
    }

    public function getFormatted(string $id, Item $item): Item
    {
        return $item->setNamedTag((new CompoundTag())->setString('ItemID', $id))->setCustomName($this->getPlayer()->getLang()->getStrings($id));
    }
}