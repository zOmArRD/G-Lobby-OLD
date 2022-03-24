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

namespace zomarrd\ghostly\lobby\utils\menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\event\Listener;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\server\ServerManager;

abstract class Chest implements Listener
{
    private InvMenu $menu;

    /** @var array<int, MenuButton> */
    private array $buttons = [];

    /** @var array<callable> */
    private array $inventoryClose = [];

    public function __construct(private string $chestName, private string $identifier = InvMenuTypeIds::TYPE_CHEST)
    {
        $this->menu = InvMenu::create($this->identifier)->setName($this->chestName)->setInventoryCloseListener(function (GhostlyPlayer $player): void {
            $closure = $this->inventoryClose[$player->getUniqueId()->toString()] ?? null;

            if ($closure !== null) {
                $closure($player);
                unset($this->inventoryClose[$player->getUniqueId()->toString()]);
            }

        })->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $button = $this->buttons[$transaction->getAction()->getSlot()] ?? null;

            if ($button !== null) {
                if ($player instanceof GhostlyPlayer) {
                    $button->click($player);
                }

                return $transaction->discard();
            }

            return $transaction->continue();
        });
    }

    public function build(GhostlyPlayer $player): void
    {
        if ($player->isOnline()) {
            $this->menu->send($player);
        }
    }

    public function addButton(MenuButton $button, int $slot): void
    {
        if ($slot < $this->menu->getInventory()->getSize()) {
            $this->menu->getInventory()->setItem($slot, $button->getItem());
            $this->buttons[$slot] = $button;
        }
    }

    public function getMenu(): InvMenu
    {
        return $this->menu;
    }

    public function getServerManager(): ServerManager
    {
        return ServerManager::getInstance();
    }
}