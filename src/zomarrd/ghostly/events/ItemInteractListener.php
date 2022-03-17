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
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use zomarrd\ghostly\entity\events\HumanInteractEvent;
use zomarrd\ghostly\entity\type\HumanType;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\menu\Menu;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;

final class ItemInteractListener implements Listener
{
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

                    if ($player->hasCooldown(2)) {
                        return;
                    }

                    $this->handleInteract($player, $item);
                    $player->setCooldown();
                }
            }

            if ($trData instanceof UseItemOnEntityTransactionData) {
                $i = $trData->getActionType();
                if (($i === UseItemOnEntityTransactionData::ACTION_INTERACT) || ($i === UseItemOnEntityTransactionData::ACTION_ATTACK)) {
                    $target = $player->getWorld()->getEntity($trData->getActorRuntimeId());

                    if ($target instanceof HumanType && !$player->hasCooldown(1.5)) {
                        $interactEvent = new HumanInteractEvent($target, $player);

                        if (!$interactEvent->isCancelled()) {
                            $interactEvent->call();
                        }

                        $player->setCooldown();
                    }
                }
            }
        }
    }

    public function handleInteract(GhostlyPlayer $player, Item $item): void
    {
        $itemId = $item->getNamedTag()->getString("itemId", "");
        switch ($itemId) {
            case "item-lobby":
                if ($player->hasClassicProfile()) {
                    Menu::LOBBY_SELECTOR()->sendType($player);
                } else {
                    Menu::LOBBY_SELECTOR()->sendType($player, Menu::FORM_TYPE);
                }

                $player->sendSound(LevelSoundEvent::DROP_SLOT);
                break;
            case "item-servers":
                if ($player->hasClassicProfile()) {
                    Menu::SERVER_SELECTOR()->sendType($player);
                } else {
                    Menu::SERVER_SELECTOR()->sendType($player, Menu::FORM_TYPE);
                }

                $player->sendSound(LevelSoundEvent::DROP_SLOT);
                break;
            case "item-queue":
                Ghostly::getQueueManager()->remove($player, $player->getQueue()?->getServer());
                $player->getLobbyItems();
                $player->sendTranslated(LangKey::QUEUE_PLAYER_LEFT);
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
        $item = $event->getItem();
        $block = $event->getBlock();
        $event->cancel();

        if (!$player instanceof GhostlyPlayer) {
            return;
        }

        if ($player->isOp()) {
            $event->uncancel();
        }

        if ($player->hasCooldown(2)) {
            return;
        }

        $this->handleInteract($player, $item);

        $player->setCooldown();
    }
}