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

use pocketmine\block\DoublePlant;
use pocketmine\block\Flower;
use pocketmine\block\FlowerPot;
use pocketmine\block\TallGrass;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
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

		if ($packet instanceof InventoryTransactionPacket || $player instanceof GhostlyPlayer) {
			$pn = $player->getName();

			if (!isset($packet->trData)) {
				return;
			}

			$trData = $packet->trData;

			if ($trData instanceof UseItemTransactionData) {
				$i1 = $trData->getActionType();
				if ($i1 === UseItemTransactionData::ACTION_CLICK_AIR || $i1 === UseItemTransactionData::ACTION_CLICK_BLOCK) {
					$item = $player->getInventory()->getItemInHand();
					$itemManager = $player->getItemManager();

					if (!isset($this->item_cooldown[$pn]) || time() - $this->item_cooldown[$pn] >= 2) {
						$this->handleInteract($player, $item);
						$this->item_cooldown[$pn] = time();
					}
				}
			}

			if ($trData instanceof UseItemOnEntityTransactionData) {
				$i = $trData->getActionType();
				if (($i === UseItemOnEntityTransactionData::ACTION_INTERACT) || ($i === UseItemOnEntityTransactionData::ACTION_ATTACK)) {
					$target = $player->getWorld()->getEntity($trData->getActorRuntimeId());

					if ($target instanceof HumanType && (!isset($this->entity_cooldown[$pn]) || time() - $this->entity_cooldown[$pn] >= 1.5)) {
						$interactEvent = new HumanInteractEvent($target, $player);
						if (!$interactEvent->isCancelled()) {
							$interactEvent->call();
						}

						$this->entity_cooldown[$pn] = time();
					}
				}
			}
		}
	}

	public function handleInteract(GhostlyPlayer $player, Item $item): void
	{
		$itemManager = $player->getItemManager();
		$itemId = $item->getNamedTag()->getString("itemId", "");
		switch ($itemId) {
			case "item-lobby":
				if ($player->hasClassicProfile()) {
					Menu::LOBBY_SELECTOR_GUI()->build($player);
				} else {
					Menu::LOBBY_SELECTOR_FORM()->build($player);
				}
				$player->sendSound(LevelSoundEvent::DROP_SLOT);
				break;
			case "item-servers":
				if ($player->hasClassicProfile()) {
					Menu::SERVER_SELECTOR_GUI()->send($player);
				} else {
					/*TODO SEND THE MENU IN FORM EDITION*/
				}
				$player->sendSound(LevelSoundEvent::DROP_SLOT);
				break;
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

		if (!$player->canInteractItem()) {
			return;
		}

		if (!$player->isOp()) {
			$event->cancel();
		}

		$pn = $player->getName();
		$item = $event->getItem();
		$block = $event->getBlock();

		if ($player->hasCooldown(2)) {
			return;
		}

		if ($block instanceof TallGrass || $block instanceof Flower || $block instanceof FlowerPot || $block instanceof DoublePlant) {
			return; // SMALL HACK TO AVOID THE BUG OF THE GUI MENUS!
		}

		$this->handleInteract($player, $item);

		$player->setCooldown();
	}
}