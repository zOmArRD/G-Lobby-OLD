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

namespace zomarrd\ghostly\menu\lobbyselector;

use pocketmine\item\VanillaItems;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\server\Server;
use zomarrd\ghostly\server\ServerManager;
use zomarrd\ghostly\utils\menu\Chest;
use zomarrd\ghostly\utils\menu\MenuButton;

class LobbySelectorGUI extends Chest
{
	public function __construct()
	{
		parent::__construct('Lobby Selector');
	}

	public function build(GhostlyPlayer $player): void
	{
		$this->addLobbyServers();
		parent::build($player);
	}

	private array $item_cooldown = [];

	public function addServer(Server $server, int $slot): void
	{
		$item = VanillaItems::NETHER_STAR()->setCustomName("§r§a" . $server->getName());

		if (!$server->isOnline()) {
			$item->setCustomName("§r§c" . $server->getName())->setLore(["§r§c" . "This server is currently offline!"]);
		} else {
			$item->setLore(["§r" . "§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}\n\n§a" .
				"Click to connect to this server!"]);
		}

		if ($server->getName() === Ghostly::SERVER && $server->isOnline()) {
			$item->setLore(["§r" .
				"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}\n\n§c" .
				"You are already connected!"
			]);
		}

		$cooldown = $this->item_cooldown;
		$this->addButton(new MenuButton($item, function (GhostlyPlayer $player) use ($server, $cooldown): void {
			if (isset($this->item_cooldown[$player->getName()]) && time() - $this->item_cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->transfer_to_lobby($server);
			$this->item_cooldown[$player->getName()] = time();
		}), $slot);
	}

	public function addLobbyServers(): void
	{
		$slot = 0;
		$servers = $this->getServerManager()->getServers();
		$current = $this->getServerManager()->getCurrentServer();

		if (isset($current)) {
			$this->addServer($current, $slot);
			$slot++;
		}

		foreach ($servers as $server) {
			if ($server->getCategory() !== "Lobby") {
				continue;
			}

			$this->addServer($server, $slot);
			$slot++;
		}
	}

	public function getServerManager(): ServerManager
	{
		return ServerManager::getInstance();
	}
}