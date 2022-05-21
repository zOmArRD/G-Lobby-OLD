<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 19/2/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */

namespace zomarrd\ghostly\lobby\menu\lobbyselector;

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
use zomarrd\ghostly\lobby\server\ServerManager;
use zomarrd\ghostly\lobby\utils\menu\MenuButton;

final class LobbySelector
{
    private InvMenu $menu;

    /** @var array<int, MenuButton> */
    private array $buttons = [];

    public function register(): void
    {
        $this->menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST)->setName('§l§cGhostly §f» §r§6Lobby Selector')->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
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
        $servers = ServerManager::getInstance()->getServers();
        $servers[] = ServerManager::getInstance()->getCurrentServer();

        foreach ($servers as $server) {
            if (!isset($server)) {
                continue;
            }

            if ($server->getCategory() !== 'Lobby') {
                continue;
            }

            if ($server->getName() === 'Lobby-1') {
                $this->addServer($server, 10);
            }

            if ($server->getName() === 'Lobby-2') {
                $this->addServer($server, 11);
            }

            if ($server->getName() === 'Lobby-3') {
                $this->addServer($server, 12);
            }
        }
    }

    public function addServer(Server $server, int $slot): void
    {
        $item = VanillaItems::NETHER_STAR()->setCustomName('§r§a' . $server->getName());

        if ($server->isOnline()) {
            $arrayOriginal = Ghostly::$server_items->get('Lobby')['online'];
            $arrayOriginal[0] = sprintf("§r§7Players: §f%s§7/§f%s\n", $server->getOnlinePlayers(), $server->getMaxPlayers());
            $item->setLore($arrayOriginal);
        } else {
            $item->setLore(Ghostly::$server_items->get('Lobby')['offline']);
        }

        if ($server->getName() === Server['name']) {
            $arrayOriginal = Ghostly::$server_items->get('Lobby')['already-connected'];
            $arrayOriginal[0] = sprintf("§r§7Players: §f%s§7/§f%s\n", $server->getOnlinePlayers(), $server->getMaxPlayers());
            $item->setLore($arrayOriginal);
        }

        $this->addButton(new MenuButton($item, function(GhostlyPlayer $player) use ($server): void {
            $this->callable($player, $server);
        }), $slot);
    }

    public function callable(GhostlyPlayer $player, Server|string $server): void
    {
        $player->sendTranslated(LangKey::SERVER_SEARCHING);
        $player->transferTo($server);
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

                    $player->transferTo($data);
                }
            });

            $form->setTitle('§l§cGhostly §f» §r§6Lobby Selector');
            $servers = ServerManager::getInstance()->getServers();

            $this->addServerButton(ServerManager::getInstance()->getCurrentServer(), $form);
            foreach ($servers as $server) {
                if ($server->getCategory() !== 'Lobby') {
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

        if ($server->getName() === Server['name']) {
            $text = sprintf("§a%s §7[§f%s§7/§f%s§7]\n§cYou are already connected here!", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers());
        } else {
            $text = $server->isOnline() ? sprintf("§a%s §7[§f%s§7/§7%s§7]\n§eClick to transfer!", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers()) : sprintf("§a%s §7[§f%s§f/§7%s§7]\n§cOFFLINE", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers());
        }

        if ($server->isWhitelisted()) {
            $text = sprintf("§a%s §7[§f%s§7/§7%s§7]\n§cWHITELISTED", $server->getName(), $server->getOnlinePlayers(), $server->getMaxPlayers());
        }

        $form->addButton($text, $form::IMAGE_TYPE_NULL, '', $server->getName());
    }

    public function getMenu(): InvMenu
    {
        return $this->menu;
    }
}