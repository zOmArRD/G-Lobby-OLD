<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 28/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\menu\serverselector;

use GhostlyMC\FormAPI\SimpleForm;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\menu\Menu;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\server\Server;
use zomarrd\ghostly\lobby\server\ServerItems;
use zomarrd\ghostly\lobby\server\ServerList;
use zomarrd\ghostly\lobby\server\ServerManager;
use zomarrd\ghostly\lobby\utils\menu\MenuButton;

final class ServerSelector
{
    private InvMenu $menu;

    /** @var array<int, MenuButton> */
    private array $buttons = [];

    public function register(): void
    {
        $this->menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)->setName('§l§cGhostly §f» §r§6Server Selector')->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
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

    public function prepare(): void
    {
        $this->addButton(new MenuButton(ServerItems::get(ServerList::PRACTICE, VanillaItems::DIAMOND_SWORD()), function(GhostlyPlayer $player): void {
            $this->callable($player, ServerList::PRACTICE);
        }), 12);

        $this->addButton(new MenuButton(ServerItems::get(ServerList::COMBO, VanillaItems::ENDER_PEARL()), function(GhostlyPlayer $player): void {
            $this->callable($player, ServerList::COMBO);
        }), 14);

        $this->addButton(new MenuButton(ServerItems::get(ServerList::HCF, VanillaItems::DIAMOND_PICKAXE()), function(GhostlyPlayer $player): void {
            $this->callable($player, ServerList::HCF);
        }), 38);

        $this->addButton(new MenuButton(ServerItems::get(ServerList::KITMAP, VanillaItems::GOLDEN_PICKAXE()), function(GhostlyPlayer $player): void {
            $this->callable($player, ServerList::KITMAP);
        }), 39);

        $this->addButton(new MenuButton(ServerItems::get(ServerList::UHC, VanillaItems::GOLDEN_APPLE()), function(GhostlyPlayer $player): void {
            $this->callable($player, ServerList::UHC);
        }), 41);

        $this->addButton(new MenuButton(ServerItems::get(ServerList::UHCRUN, VanillaItems::APPLE()), function(GhostlyPlayer $player): void {
            $this->callable($player, ServerList::UHCRUN);
        }), 42);
    }

    public function callable(GhostlyPlayer $player, Server|string $server): void
    {
        $player->sendTranslated(LangKey::SERVER_SEARCHING);
        Ghostly::getQueueManager()->add($player, $server);
        $player->closeInventory();
    }

    public function sendType(GhostlyPlayer $player, string $type = Menu::GUI_TYPE): void
    {
        if ($type === Menu::GUI_TYPE) {
            $this->menu->send($player);
        } else {
            $form = new SimpleForm(function(GhostlyPlayer $player, $data): void {
                if (isset($data)) {
                    if ($data === 'close') {
                        return;
                    }

                    $player->sendTranslated(LangKey::SERVER_SEARCHING);
                    Ghostly::getQueueManager()->add($player, $data);
                }
            });

            $form->setTitle('§l§cGhostly §f» §r§6Server Selector');
            $servers = ServerManager::getInstance()->getServers();

            foreach ($servers as $server) {
                if ($server->getCategory() === 'Lobby') {
                    continue;
                }

                $this->addServerButton($server, $form);
            }

            $form->addButton($player->getTranslation(LangKey::FORM_BUTTON_CLOSE), $form::IMAGE_TYPE_NULL, '', 'close');
            $player->sendForm($form);
        }
    }

    public function addServerButton(Server $server, SimpleForm $form): void
    {
        $text = '§r';
        $text = $server->isOnline() ? ($text . sprintf("§a%s §7[§f%s§7/§7%s§7]\n§eClick to transfer!", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers())) : ($text . sprintf("§a%s §7[§f%s§f/§7%s§7]\n§cOFFLINE", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers()));

        if ($server->isWhitelisted()) {
            $text = sprintf("§a%s §7[§f%s§7/§f%s§7]\n§cWHITELISTED", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers());
        }

        $form->addButton($text, $form::IMAGE_TYPE_NULL, '', $server->getName());
    }

    public function getMenu(): InvMenu
    {
        return $this->menu;
    }
}