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

namespace zomarrd\ghostly\events;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use zomarrd\ghostly\entity\events\HumanInteractEvent;
use zomarrd\ghostly\entity\type\HumanType;
use zomarrd\ghostly\menu\Menu;
use zomarrd\ghostly\player\GhostlyPlayer;

final class ItemInteractListener implements Listener
{
	private array $item_cooldown;

	private array $entity_cooldown;

	public function npc_listener_handler(DataPacketReceiveEvent $event): void
	{
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();
		if (!$packet instanceof InventoryTransactionPacket && !$player instanceof GhostlyPlayer) {
			return;
		}

		$pn = $player->getName();

		if (isset($packet->trData)) {
			$trData = $packet->trData;
		} else {
			return;
		}

		if ($trData instanceof UseItemTransactionData) {
			switch ($trData->getActionType()) {
				case UseItemTransactionData::ACTION_CLICK_AIR:
				case UseItemTransactionData::ACTION_CLICK_BLOCK:
					$item = $player->getInventory()->getItemInHand();
					$itemManager = $player->getItemManager();

					if (!isset($this->item_cooldown[$pn]) || time() - $this->item_cooldown[$pn] >= 2) {
						switch (true) {
							case $item->equals($itemManager->get('lobby-selector')):
								var_dump(2);
								if ($player->hasClassicProfile()) {
									Menu::LOBBY_SELECTOR_GUI()->build($player);
								} else {
									Menu::LOBBY_SELECTOR_FORM()->build($player);
								}
								break;
						}
						$this->item_cooldown[$pn] = time();
					}
					break;
			}
			return;
		}

		if ($trData instanceof UseItemOnEntityTransactionData) {
			switch ($trData->getActionType()) {
				case UseItemOnEntityTransactionData::ACTION_INTERACT:
				case UseItemOnEntityTransactionData::ACTION_ATTACK:
					//case UseItemOnEntityTransactionData::ACTION_ITEM_INTERACT:
					$target = $player->getWorld()->getEntity($trData->getActorRuntimeId());
					if ($target instanceof HumanType) {
						if (!isset($this->entity_cooldown[$pn]) || time() - $this->entity_cooldown[$pn] >= 1.5) {

							$event = new HumanInteractEvent($target, $player);
							if (!$event->isCancelled()) {
								$event->call();
							}
							$this->entity_cooldown[$pn] = time();
						}
					}
					break;
			}
		}
	}

	public function slot_change(InventoryTransactionEvent $event): void
	{
		$player = $event->getTransaction()->getSource();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}
		foreach ($event->getTransaction()->getActions() as $action) {
			if (($action instanceof SlotChangeAction) && !$player->isOp()) {
				$event->cancel();
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}
		if (!$player->isOp()) {
			$event->cancel();
		}
		$pn = $player->getName();
		$item = $event->getItem();
		$itemManager = $player->getItemManager();

		if (!isset($this->item_cooldown[$pn]) || time() - $this->item_cooldown[$pn] >= 2) {
			switch (true) {
				case $item->equals($itemManager->get('lobby-selector')):
					var_dump(1);
					if ($player->hasClassicProfile()) {
						Menu::LOBBY_SELECTOR_GUI()->build($player);
					} else {
						Menu::LOBBY_SELECTOR_FORM()->build($player);
					}
					break;
			}
			$this->item_cooldown[$pn] = time();
		}
	}
}