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
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\server\Server;
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
		$close = VanillaItems::RED_BED()->setCustomName($player->getTranslation(LangKey::FORM_BUTTON_CLOSE));

		$this->addButton(new MenuButton($close, function (GhostlyPlayer $player): void {
			$player->closeInventory();
		}), 0);

		$this->addLobbyServers();
		parent::build($player);
	}

	/**
	 * @param Server $server
	 * @param int    $slot
	 *
	 * @return void
	 * @todo add whitelisted lore
	 */
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

		$this->addButton(new MenuButton($item, function (GhostlyPlayer $player) use ($server): void {
			$player->closeInventory();
			$player->transferTo($server);
		}), $slot);
	}

	public function addLobbyServers(): void
	{
		$slot = 10;
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
}