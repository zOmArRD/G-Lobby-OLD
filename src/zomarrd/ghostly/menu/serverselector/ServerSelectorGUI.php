<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 13/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\menu\serverselector;

use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\utils\menu\Chest;
use zomarrd\ghostly\utils\menu\MenuButton;

final class ServerSelectorGUI extends Chest
{
	private array $item_cooldown = [];

	public function __construct()
	{
		parent::__construct('Server Selector', InvMenuTypeIds::TYPE_DOUBLE_CHEST);
	}

	public function build(GhostlyPlayer $player): void
	{
		$cooldown = $this->item_cooldown;

		$close = VanillaItems::RED_BED()->setCustomName($player->getTranslation(LangKey::FORM_BUTTON_CLOSE));
		$this->addButton(new MenuButton($close, function (GhostlyPlayer $player): void {
			$player->closeInventory();
		}), 0);

		$this->addButton(new MenuButton($this->getFormattedItem('Practice', VanillaItems::DIAMOND_SWORD()), function (GhostlyPlayer $player) use ($cooldown): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('Practice');
			$cooldown[$player->getName()] = time();
		}), 13);

		$this->addButton(new MenuButton($this->getFormattedItem('Combo', VanillaItems::ENDER_PEARL()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('Combo');
			$cooldown[$player->getName()] = time();
		}), 15);

		$head = VanillaItems::PLAYER_HEAD();
		$head->setCustomName("§r§c{$player->getName()}");
		$head->setLore(
			[
				"§r§8Profile\n\n" .
				"§4Lang: §7{$player->getLang()->getName()}\n" .
				"§4Shop Name: §7{$player->getName()}\n" .
				"§4Permission: §7Default\n" .
				"§4Server: §7" . Ghostly::SERVER . "\n\n" .
				"§4Store: §7store.ghostlymc.live"
			]
		);

		//$this->addButton(new MenuButton($head, static function (): void{}), 53);
		parent::build($player);
	}

	/**
	 * @param string $server_name
	 * @param Item   $item
	 *
	 * @return Item
	 * @todo Create the items in a more optimal way in the future!
	 */
	public function getFormattedItem(string $server_name, Item $item): Item
	{
		$items = [];
		$server = $this->getServerManager()->getServerByName($server_name);

		if (!isset($server)) {
			return $item;
		}
		$br = "\n";

		switch ($server->getName()) {
			case "Practice":
				$item->setCustomName("§r§l§c" . "Practice");

				if (!$server->isOnline()) {
					$item->setLore(
						[
							"§r§8Competitive $br$br" .
							"§f· Ranked & UnRanked Matches $br" .
							"· Leaderboard Prize $br" .
							"· Tournaments & Events $br" .
							"· Parties & HCF Team Fight $br$br" .
							"§c" . "This server is currently offline!"
						]
					);
					break;
				}

				if ($server->isWhitelist()) {
					$item->setLore(
						[
							"§r§8Competitive $br$br" .
							"§f· Ranked & UnRanked Matches $br" .
							"· Leaderboard Prize $br" .
							"· Tournaments & Events $br" .
							"· Parties & HCF Team Fight $br$br" .
							"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
							"§cWHITELISTED"
						]
					);
					break;
				}

				$item->setLore(
					[
						"§r§8Competitive $br$br" .
						"§f· Ranked & UnRanked Matches $br" .
						"· Leaderboard Prize $br" .
						"· Tournaments & Events $br" .
						"· Parties & HCF Team Fight $br$br" .
						"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
						"§eClick to join to Practice."
					]
				);
				break;

			case "Combo":
				$item->setCustomName("§r§l§c" . "Combo");

				if (!$server->isOnline()) {
					$item->setLore(
						[
							"§r§8Competitive $br$br" .
							"§f· Custom Kits $br" .
							"· Leaderboard $br" .
							"· Kill-Streak & Bounties $br$br" .
							"§c" . "This server is currently offline!"
						]
					);
					break;
				}

				if ($server->isWhitelist()) {
					$item->setLore(
						[
							"§r§8Competitive $br$br" .
							"§f· Custom Kits $br" .
							"· Leaderboard $br" .
							"· Kill-Streak & Bounties $br$br" .
							"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
							"§cWHITELISTED"
						]
					);
					break;
				}

				$item->setLore(
					[
						"§r§8Competitive $br$br" .
						"§f· Custom Kits $br" .
						"· Leaderboard $br" .
						"· Kill-Streak & Bounties $br$br" .
						"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
						"§eClick to join to Practice."
					]
				);
				break;
		}

		return $item;
	}
}