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

namespace zomarrd\ghostly\entity\type;

use Exception;
use pocketmine\entity\ExperienceManager;
use pocketmine\entity\Human;
use pocketmine\entity\HungerManager;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerEnderInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\Limits;

final class HumanType extends Human
{
	public $canCollide = false;
	protected $immobile = true;
	protected $gravity = 0.0;
	protected string $server_name;
	protected string $npcId;

	public function __construct(Location $location, Skin $skin, CompoundTag $nbt)
	{
		$this->npcId = $nbt->getString("npcId", "");
		$this->server_name = $nbt->getString("server_name", "");
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

		$nbt->setInt("foodLevel", (int)$this->hungerManager->getFood());
		$nbt->setFloat("foodExhaustionLevel", $this->hungerManager->getExhaustion());
		$nbt->setFloat("foodSaturationLevel", $this->hungerManager->getSaturation());
		$nbt->setInt("foodTickTimer", $this->hungerManager->getFoodTickTimer());

		$nbt->setInt("XpLevel", $this->xpManager->getXpLevel());
		$nbt->setFloat("XpP", $this->xpManager->getXpProgress());
		$nbt->setInt("XpTotal", $this->xpManager->getLifetimeTotalXp());
		$nbt->setInt("XpSeed", $this->xpSeed);

		$inventoryTag = new ListTag([], NBT::TAG_Compound);
		$nbt->setTag("Inventory", $inventoryTag);
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

			$nbt->setInt("SelectedInventorySlot", $this->inventory->getHeldItemIndex());
		}
		$offHandItem = $this->offHandInventory->getItem(0);
		if (!$offHandItem->isNull()) {
			$nbt->setTag("OffHandItem", $offHandItem->nbtSerialize());
		}

		if ($this->enderInventory !== null) {
			/** @var CompoundTag[] $items */
			$items = [];

			$slotCount = $this->enderInventory->getSize();
			for ($slot = 0; $slot < $slotCount; ++$slot) {
				$item = $this->enderInventory->getItem($slot);
				if (!$item->isNull()) {
					$items[] = $item->nbtSerialize($slot);
				}
			}

			$nbt->setTag("EnderChestInventory", new ListTag($items, NBT::TAG_Compound));
		}

		if ($this->skin !== null) {
			$nbt->setTag("Skin", CompoundTag::create()
				->setString("Name", $this->skin->getSkinId())
				->setByteArray("Data", $this->skin->getSkinData())
				->setByteArray("CapeData", $this->skin->getCapeData())
				->setString("GeometryName", $this->skin->getGeometryName())
				->setByteArray("GeometryData", $this->skin->getGeometryData())
			);
		}

		$nbt->setString("server_name", $this->server_name);
		$nbt->setString("npcId", $this->npcId);

		return $nbt;
	}

	/**
	 * @throws Exception
	 */
	protected function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);

		$this->server_name = $nbt->getString("server_name", "");
		$this->npcId = $nbt->getString("npcId", "");

		$this->hungerManager = new HungerManager($this);
		$this->xpManager = new ExperienceManager($this);

		$this->inventory = new PlayerInventory($this);
		$syncHeldItem = function (): void {
			foreach ($this->getViewers() as $viewer) {
				$viewer->getNetworkSession()->onMobMainHandItemChange($this);
			}
		};
		$this->inventory->getListeners()->add(new CallbackInventoryListener(
			function (int $slot) use ($syncHeldItem): void {
				if ($slot === $this->inventory->getHeldItemIndex()) {
					$syncHeldItem();
				}
			},
			function (Inventory $unused, array $oldItems) use ($syncHeldItem): void {
				if (array_key_exists($this->inventory->getHeldItemIndex(), $oldItems)) {
					$syncHeldItem();
				}
			}
		));
		$this->offHandInventory = new PlayerOffHandInventory($this);
		$this->enderInventory = new PlayerEnderInventory($this);
		$this->initHumanData($nbt);

		$inventoryTag = $nbt->getListTag("Inventory");
		if ($inventoryTag !== null) {
			$armorListeners = $this->armorInventory->getListeners()->toArray();
			$this->armorInventory->getListeners()->clear();
			$inventoryListeners = $this->inventory->getListeners()->toArray();
			$this->inventory->getListeners()->clear();

			/** @var CompoundTag $item */
			foreach ($inventoryTag as $i => $item) {
				$slot = $item->getByte("Slot");
				if ($slot >= 100 && $slot < 104) { //Armor
					$this->armorInventory->setItem($slot - 100, Item::nbtDeserialize($item));
				} else if ($slot >= 9 && $slot < $this->inventory->getSize() + 9) {
					$this->inventory->setItem($slot - 9, Item::nbtDeserialize($item));
				}
			}

			$this->armorInventory->getListeners()->add(...$armorListeners);
			$this->inventory->getListeners()->add(...$inventoryListeners);
		}

		$offHand = $nbt->getCompoundTag("OffHandItem");
		if ($offHand !== null) {
			$this->offHandInventory->setItem(0, Item::nbtDeserialize($offHand));
		}

		$this->offHandInventory->getListeners()->add(CallbackInventoryListener::onAnyChange(function (): void {
			foreach ($this->getViewers() as $viewer) {
				$viewer->getNetworkSession()->onMobOffHandItemChange($this);
			}
		}));

		$enderChestInventoryTag = $nbt->getListTag("EnderChestInventory");
		if ($enderChestInventoryTag !== null) {
			/** @var CompoundTag $item */
			foreach ($enderChestInventoryTag as $i => $item) {
				$this->enderInventory->setItem($item->getByte("Slot"), Item::nbtDeserialize($item));
			}
		}

		$this->inventory->setHeldItemIndex($nbt->getInt("SelectedInventorySlot", 0));
		$this->inventory->getHeldItemIndexChangeListeners()->add(function (): void {
			foreach ($this->getViewers() as $viewer) {
				$viewer->getNetworkSession()->onMobMainHandItemChange($this);
			}
		});

		$this->hungerManager->setFood((float)$nbt->getInt("foodLevel", (int)$this->hungerManager->getFood()));
		$this->hungerManager->setExhaustion($nbt->getFloat("foodExhaustionLevel", $this->hungerManager->getExhaustion()));
		$this->hungerManager->setSaturation($nbt->getFloat("foodSaturationLevel", $this->hungerManager->getSaturation()));
		$this->hungerManager->setFoodTickTimer($nbt->getInt("foodTickTimer", $this->hungerManager->getFoodTickTimer()));

		$this->xpManager->setXpAndProgressNoEvent(
			$nbt->getInt("XpLevel", 0),
			$nbt->getFloat("XpP", 0.0)
		);

		$this->xpManager->setLifetimeTotalXp($nbt->getInt("XpTotal", 0));

		if (($xpSeedTag = $nbt->getTag("XpSeed")) instanceof IntTag) {
			$this->xpSeed = $xpSeedTag->getValue();
		} else {
			$this->xpSeed = random_int(Limits::INT32_MIN, Limits::INT32_MAX);
		}
	}
}