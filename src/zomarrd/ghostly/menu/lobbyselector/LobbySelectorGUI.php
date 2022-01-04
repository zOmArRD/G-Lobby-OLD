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
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\server\LocalServer;
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

	public function addServer(string $serverName, int $slot): void
	{
		$server = $this->getServerManager()->getServerByName($serverName);
		$item = VanillaItems::NETHER_STAR()->setCustomName("§r§a" . $serverName);
		$currentServer = $this->getServerManager()->getCurrentServer();


		if ($currentServer !== null && $serverName === $currentServer->getServerName()) {
			$item->setLore(["§r" .
				"§7Players: §f{$currentServer->getPlayers()}§7/§f{$currentServer->getMaxPlayers()}\n\n§c" .
				"You are already connected!"
			]);
		} elseif ($server === null || !$server->isOnline()) {
			$item->setCustomName("§r§c" . $serverName)->setLore(["§r§c" . "This server is currently offline!"]);
		} else {
			$item->setLore(["§r" . "§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}\n\n§a" .
				"Click to connect to this server!"]);
		}

		$this->addButton(new MenuButton($item, function (GhostlyPlayer $player) use ($item): void {
			$player->sendMessage($item->getName());
		}), $slot);
	}

	public function addLobbyServers(): void
	{
		$slot = 0;
		$servers = $this->getLocalServer()->getServers();
		foreach ($servers as $server) {
			if ($server["category"] === "Lobby") {
				$this->addServer($server["server_name"], $slot);
				$slot++;
			}
		}
	}

	public function getServerManager(): ServerManager
	{
		return ServerManager::getInstance();
	}

	public function getLocalServer(): LocalServer
	{
		return LocalServer::getInstance();
	}
}