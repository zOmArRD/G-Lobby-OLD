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

namespace zomarrd\ghostly\lobby\events;

use GhostlyMC\DatabaseAPI\mysql\MySQL;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use zomarrd\ghostly\lobby\database\mysql\queries\UpdateRowQuery;
use zomarrd\ghostly\lobby\entity\events\HumanInteractEvent;
use zomarrd\ghostly\lobby\entity\type\HumanType;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\menu\Menu;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\utils\VISIBILITY;

final class ItemInteractListener implements Listener
{
    public function npc_listener_handler(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if ($packet instanceof InventoryTransactionPacket || $player instanceof GhostlyPlayer) {

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
                            $player->setCooldown();
                        }
                    }
                }
            }
        }
    }

    /** @noinspection DuplicatedCode */
    public function handleInteract(GhostlyPlayer $player, Item $item): void
    {
        $itemId = $item->getNamedTag()->getString('ItemID', '');
        $inventory = $player->getInventory();

        switch ($itemId) {
            case LangKey::ITEM_LOBBY_SELECTOR:
                if ($player->hasClassicProfile()) {
                    Menu::LOBBY_SELECTOR()->sendType($player);
                } else {
                    Menu::LOBBY_SELECTOR()->sendType($player, Menu::FORM_TYPE);
                }

                $player->sendSound(LevelSoundEvent::DROP_SLOT);
                break;
            case LangKey::ITEM_SERVER_SELECTOR:
                if ($player->hasClassicProfile()) {
                    Menu::SERVER_SELECTOR()->sendType($player);
                } else {
                    Menu::SERVER_SELECTOR()->sendType($player, Menu::FORM_TYPE);
                }

                $player->sendSound(LevelSoundEvent::DROP_SLOT);
                break;
            case LangKey::ITEM_QUEUE_EXIT:
                $player->sendTranslated(LangKey::QUEUE_EXITED, ['{SERVER-NAME}' => $player->getQueue()?->getServer()]);
                Ghostly::getQueueManager()->remove($player, $player->getQueue()?->getServer());
                $player->getLobbyItems();
                break;
            case LangKey::ITEM_VISIBILITY_ALL: // change to staff.
                $inventory->setItem(6, VanillaItems::air());

                MySQL::runAsync(new UpdateRowQuery([
                    'visibilityMode' => VISIBILITY::STAFF,
                ], 'xuid', $player->getXuid(), 'ghostly_playerdata'),
                    static function() use ($player, $inventory): void {
                        $player->setVisibilityMode(VISIBILITY::STAFF);
                        $inventory->setItem(6, $player->getItemManager()->get(LangKey::ITEM_VISIBILITY_STAFF));
                    }
                );
                break;
            case LangKey::ITEM_VISIBILITY_STAFF: // change to nobody.
                $inventory->setItem(6, VanillaItems::air());

                MySQL::runAsync(new UpdateRowQuery([
                    'visibilityMode' => VISIBILITY::NOBODY,
                ], 'xuid', $player->getXuid(), 'ghostly_playerdata'),
                    static function() use ($player, $inventory): void {
                        $player->setVisibilityMode(VISIBILITY::NOBODY);
                        $inventory->setItem(6, $player->getItemManager()->get(LangKey::ITEM_VISIBILITY_NONE));
                    }
                );
                break;
            case LangKey::ITEM_VISIBILITY_NONE: // change to all.
                $inventory->setItem(6, VanillaItems::air());

                MySQL::runAsync(new UpdateRowQuery([
                    'visibilityMode' => VISIBILITY::ALL,
                ], 'xuid', $player->getXuid(), 'ghostly_playerdata'),
                    static function() use ($player, $inventory): void {
                        $player->setVisibilityMode(VISIBILITY::ALL);
                        $inventory->setItem(6, $player->getItemManager()->get(LangKey::ITEM_VISIBILITY_ALL));
                    }
                );
                break;
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event): void
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