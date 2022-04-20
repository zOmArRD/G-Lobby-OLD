<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 24/3/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\player;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\lobby\utils\menu\MenuButton;

final class Settings
{
    private InvMenu $menu;

    /** @var array<int, MenuButton> */
    private array $buttons = [];

    public function __construct(private GhostlyPlayer $player) {}

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }

    public function registerChest(): void
    {
        $this->menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)->setName('Global Settings | 1/1')->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $button = $this->buttons[$transaction->getAction()->getSlot()] ?? null;

            if (isset($button)) {
                if ($player instanceof GhostlyPlayer) {
                    $button->click($player);
                }

                return $transaction->discard();
            }

            return $transaction->continue();
        });

        $close = VanillaItems::RED_BED()->setCustomName('§r§cClose');
        $this->addButton(new MenuButton($close, function(GhostlyPlayer $player): void {
            $player->closeInventory();
        }), 0);
    }

    public function addButton(MenuButton $button, int $slot): void
    {
        if ($slot < $this->menu->getInventory()->getSize()) {
            $this->menu->getInventory()->setItem($slot, $button->getItem());
            $this->buttons[$slot] = $button;
        }
    }
}