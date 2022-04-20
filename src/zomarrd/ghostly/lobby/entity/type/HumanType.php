<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 9/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\entity\type;

use Exception;
use pocketmine\entity\Human;
use pocketmine\entity\HungerManager;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

final class HumanType extends Human
{
    public $canCollide = false;
    protected $immobile = true;
    protected $gravity = 0.0;
    protected string $server_name;
    protected string $npcId;

    public function __construct(Location $location, Skin $skin, CompoundTag $nbt)
    {
        $this->npcId = $nbt->getString('npcId', '');
        $this->server_name = $nbt->getString('server_name', '');
        parent::__construct($location, $skin, $nbt);
        $this->setNameTagAlwaysVisible();
        $this->setNameTagVisible();
    }

    public function getNpcId(): string
    {
        return $this->npcId;
    }

    public function setNpcId(string $npcId): void
    {
        $this->npcId = $npcId;
    }

    public function getServerName(): string
    {
        return $this->server_name;
    }

    public function setServerName(string $server_name): void
    {
        $this->server_name = $server_name;
    }

    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    public function getDrops(): array
    {
        return [];
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();

        $nbt->setInt('foodLevel', (int)$this->hungerManager->getFood());
        $nbt->setFloat('foodExhaustionLevel', $this->hungerManager->getExhaustion());
        $nbt->setFloat('foodSaturationLevel', $this->hungerManager->getSaturation());
        $nbt->setInt('foodTickTimer', $this->hungerManager->getFoodTickTimer());

        $inventoryTag = new ListTag([], NBT::TAG_Compound);
        $nbt->setTag('Inventory', $inventoryTag);
        if ($this->inventory !== null) {
            //Normal inventory
            $slotCount = $this->inventory->getSize() + $this->inventory->getHotbarSize();
            for ($slot = $this->inventory->getHotbarSize(); $slot < $slotCount; ++$slot) {
                $item = $this->inventory->getItem($slot - 9);
                if (!$item->isNull()) {
                    $inventoryTag->push($item->nbtSerialize($slot));
                }
            }

            //Armor
            for ($slot = 100; $slot < 104; ++$slot) {
                $item = $this->armorInventory->getItem($slot - 100);
                if (!$item->isNull()) {
                    $inventoryTag->push($item->nbtSerialize($slot));
                }
            }

            $nbt->setInt('SelectedInventorySlot', $this->inventory->getHeldItemIndex());
        }
        $offHandItem = $this->offHandInventory->getItem(0);
        if (!$offHandItem->isNull()) {
            $nbt->setTag('OffHandItem', $offHandItem->nbtSerialize());
        }

        if ($this->skin !== null) {
            $nbt->setTag('Skin', CompoundTag::create()->setString('Name', $this->skin->getSkinId())->setByteArray('Data', $this->skin->getSkinData())->setByteArray('CapeData', $this->skin->getCapeData())->setString('GeometryName', $this->skin->getGeometryName())->setByteArray('GeometryData', $this->skin->getGeometryData()));
        }

        $nbt->setString('server_name', $this->server_name);
        $nbt->setString('npcId', $this->npcId);

        return $nbt;
    }

    /**
     * @throws Exception
     */
    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);

        $this->server_name = $nbt->getString('server_name', '');
        $this->npcId = $nbt->getString('npcId', '');

        $this->hungerManager = new HungerManager($this);

        $this->inventory = new PlayerInventory($this);

        $syncHeldItem = function(): void {
            foreach ($this->getViewers() as $viewer) {
                $viewer->getNetworkSession()->onMobMainHandItemChange($this);
            }
        };

        $this->inventory->getListeners()->add(new CallbackInventoryListener(function(Inventory $inventory, int $slot) use ($syncHeldItem): void {
            if ($slot === $this->inventory->getHeldItemIndex()) {
                $syncHeldItem();
            }
        }, function(Inventory $inventory, array $oldItems) use ($syncHeldItem): void {
            if (array_key_exists($this->inventory->getHeldItemIndex(), $oldItems)) {
                $syncHeldItem();
            }
        }));

        $this->offHandInventory = new PlayerOffHandInventory($this);
        $this->initHumanData($nbt);

        $inventoryTag = $nbt->getListTag('Inventory');
        if ($inventoryTag !== null) {
            $armorListeners = $this->armorInventory->getListeners()->toArray();
            $this->armorInventory->getListeners()->clear();
            $inventoryListeners = $this->inventory->getListeners()->toArray();
            $this->inventory->getListeners()->clear();

            foreach ($inventoryTag as $item) {
                assert($item instanceof CompoundTag);
                $slot = $item->getByte('Slot');
                if ($slot >= 100 && $slot < 104) { //Armor
                    $this->armorInventory->setItem($slot - 100, Item::nbtDeserialize($item));
                } else if ($slot >= 9 && $slot < $this->inventory->getSize() + 9) {
                    $this->inventory->setItem($slot - 9, Item::nbtDeserialize($item));
                }
            }

            $this->armorInventory->getListeners()->add(...$armorListeners);
            $this->inventory->getListeners()->add(...$inventoryListeners);
        }

        $offHand = $nbt->getCompoundTag('OffHandItem');

        if ($offHand !== null) {
            $this->offHandInventory->setItem(0, Item::nbtDeserialize($offHand));
        }

        $this->offHandInventory->getListeners()->add(CallbackInventoryListener::onAnyChange(function(): void {
            foreach ($this->getViewers() as $viewer) {
                $viewer->getNetworkSession()->onMobOffHandItemChange($this);
            }
        }));

        $this->inventory->setHeldItemIndex($nbt->getInt('SelectedInventorySlot', 0));
        $this->inventory->getHeldItemIndexChangeListeners()->add(function(): void {
            foreach ($this->getViewers() as $viewer) {
                $viewer->getNetworkSession()->onMobMainHandItemChange($this);
            }
        });

        $this->hungerManager->setFood((float)$nbt->getInt('foodLevel', (int)$this->hungerManager->getFood()));
        $this->hungerManager->setExhaustion($nbt->getFloat('foodExhaustionLevel', $this->hungerManager->getExhaustion()));
        $this->hungerManager->setSaturation($nbt->getFloat('foodSaturationLevel', $this->hungerManager->getSaturation()));
        $this->hungerManager->setFoodTickTimer($nbt->getInt('foodTickTimer', $this->hungerManager->getFoodTickTimer()));
    }
}