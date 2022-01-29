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

namespace zomarrd\ghostly\menu\serverselector;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\server\ServerManager;
use zomarrd\ghostly\utils\menu\MenuButton;

final class ServerSelectorGui
{
	private InvMenu $menu;

	/** @var array<int, MenuButton> */
	private array $buttons = [];

	private array $item_cooldown = [];

	public function register(): void
	{
		$this->menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)->setName("Server Selector")
			->setListener(
				function (InvMenuTransaction $transaction): InvMenuTransactionResult {
					$player = $transaction->getPlayer();
					$button = $this->buttons[$transaction->getAction()->getSlot()] ?? null;

					if (isset($button)) {
						if ($player instanceof GhostlyPlayer) {
							$button->click($player);
						}

						return $transaction->discard();
					}

					return $transaction->continue();
				}
			);

		$close = VanillaItems::RED_BED()->setCustomName("§r§cClose");
		$this->addButton(new MenuButton($close, function (GhostlyPlayer $player): void {
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
		$cooldown = $this->item_cooldown;

		$this->addButton(new MenuButton($this->getFormattedItem('Practice', VanillaItems::DIAMOND_SWORD()), function (GhostlyPlayer $player) use ($cooldown): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('Practice');
			$cooldown[$player->getName()] = time();
		}), 12);

		$this->addButton(new MenuButton($this->getFormattedItem('Combo', VanillaItems::ENDER_PEARL()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('Combo');
			$cooldown[$player->getName()] = time();
		}), 14);

		$this->addButton(new MenuButton($this->getFormattedItem('HCF', VanillaItems::DIAMOND_PICKAXE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('HCF');
			$cooldown[$player->getName()] = time();
		}), 38);

		$this->addButton(new MenuButton($this->getFormattedItem('KITMAP', VanillaItems::GOLDEN_PICKAXE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('KITMAP');
			$cooldown[$player->getName()] = time();
		}), 39);

		$this->addButton(new MenuButton($this->getFormattedItem('UHC', VanillaItems::GOLDEN_APPLE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('UHC');
			$cooldown[$player->getName()] = time();
		}), 41);

		$this->addButton(new MenuButton($this->getFormattedItem('UHC_RUN', VanillaItems::APPLE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo('UHC_RUN');
			$cooldown[$player->getName()] = time();
		}), 42);
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
		$server = ServerManager::getInstance()->getServerByName($server_name);

		$item->setCustomName("§r§c§l" . $server_name);

		$item->setLore(
			[
				"§r§8Competitive \n\n" .
				"§cThis server is currently in development!"
			]
		);

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
							"§6#1 §7Global ELO: §f$?? USD §9Pay§bPal$br" .
							"#2 §7Global ELO: §f??? §6G-Coins$br" .
							"#3 §7Global ELO: §f??? §6G-Coins$br$br" .
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
							"§6#1 §7Global ELO: §f$?? USD §9Pay§bPal$br" .
							"#2 §7Global ELO: §f??? §6G-Coins$br" .
							"#3 §7Global ELO: §f??? §6G-Coins$br$br" .
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
						"§6#1 §7Global ELO: §f$?? USD §9Pay§bPal$br" .
						"#2 §7Global ELO: §f1000 §6G-Coins$br" .
						"#3 §7Global ELO: §f500 §6G-Coins$br$br" .
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
							"§6#1 §7Top Kills: 1000 §6G-Coins$br$br" .
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
							"§6#1 §7Top Kills: 1000 §6G-Coins$br$br" .
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
						"§6#1 §7Top Kills: 1000 §6G-Coins$br$br" .
						"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
						"§eClick to join to Practice."
					]
				);
				break;

			case "HCF":
				$item->setCustomName("§r§l§c" . "HCF");

				if (!$server->isOnline()) {
					$item->setLore(
						[
							"§r§8Hardcore $br$br" .
							"§7Map Information:§f$br" .
							"· 20 Members, 0 Ally$br" .
							"· Sharpness I, Protection I, Power III$br$br" .
							"§aSOTW: §f01/??/2022 §7| 17:00pm CENTRAL TIME$br" .
							"§4EOTW: §f24/??/2022 §7| 17:00pm CENTRAL TIME$br$br" .
							"§6#1 §7F-Top: §f5000 §6G-Coins$br" .
							"§6#1 §7Top Kills: §f30000 §6G-Coins$br$br" .
							"§c" . "This server is currently offline!"
						]
					);
					break;
				}

				if ($server->isWhitelist()) {
					$item->setLore(
						[
							"§r§8Hardcore $br$br" .
							"§7Map Information:§f$br" .
							"· 20 Members, 0 Ally$br" .
							"· Sharpness I, Protection I, Power III$br$br" .
							"§aSOTW: §f01/??/2022 §7| 17:00pm CENTRAL TIME$br" .
							"§4EOTW: §f24/??/2022 §7| 17:00pm CENTRAL TIME$br$br" .
							"§6#1 §7F-Top: §f5000 §6G-Coins$br" .
							"§6#1 §7Top Kills: §f30000 §6G-Coins$br$br" .
							"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
							"§cWHITELISTED"
						]
					);
					break;
				}

				$item->setLore(
					[
						"§r§8Hardcore $br$br" .
						"§7Map Information:§f$br" .
						"· 20 Members, 0 Ally$br" .
						"· Sharpness I, Protection I, Power III$br$br" .
						"§aSOTW: §f01/??/2022 §7| 17:00pm CENTRAL TIME$br" .
						"§4EOTW: §f24/??/2022 §7| 17:00pm CENTRAL TIME$br$br" .
						"§6#1 §7F-Top: §f5000 §6G-Coins$br" .
						"§6#1 §7Top Kills: §f2000 §6G-Coins$br$br" .
						"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
						"§eClick to join to Practice."
					]
				);
				break;

			case "KITMAP":
				$item->setCustomName("§r§l§c" . "KitMap");

				if (!$server->isOnline()) {
					$item->setLore(
						[
							"§r§8Competitive $br$br" .
							"§7Map Information:§f$br" .
							"· 20 Members, 1 Ally$br" .
							"· Sharpness I, Protection I, Power III$br$br" .
							"§aSOTW: §f01/??/2022 §7| 17:00pm CENTRAL TIME$br" .
							"§4EOTW: §f24/??/2022 §7| 17:00pm CENTRAL TIME$br$br" .
							"§6#1 §7F-Top: §f3500 §6G-Coins$br" .
							"§6#1 §7Top Kills: §f2000 §6G-Coins$br$br" .
							"§c" . "This server is currently offline!"
						]
					);
					break;
				}

				if ($server->isWhitelist()) {
					$item->setLore(
						[
							"§r§8Competitive $br$br" .
							"§7Map Information:§f$br" .
							"· 20 Members, 1 Ally$br" .
							"· Sharpness I, Protection I, Power III$br$br" .
							"§aSOTW: §f01/??/2022 §7| 17:00pm CENTRAL TIME$br" .
							"§4EOTW: §f24/??/2022 §7| 17:00pm CENTRAL TIME$br$br" .
							"§6#1 §7F-Top: §f3500 §6G-Coins$br" .
							"§6#1 §7Top Kills: §f2000 §6G-Coins$br$br" .
							"§cWHITELISTED"
						]
					);
					break;
				}

				$item->setLore(
					[
						"§r§8Competitive $br$br" .
						"§7Map Information:§f$br" .
						"· 20 Members, 1 Ally$br" .
						"· Sharpness I, Protection I, Power III$br$br" .
						"§aSOTW: §f01/??/2022 §7| 17:00pm CENTRAL TIME$br" .
						"§4EOTW: §f24/??/2022 §7| 17:00pm CENTRAL TIME$br$br" .
						"§6#1 §7F-Top: §f3500 §6G-Coins$br" .
						"§6#1 §7Top Kills: §f2000 §6G-Coins$br$br" .
						"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
						"§eClick to join to Practice."
					]
				);
				break;

			case "UHC":
				$item->setCustomName("§r§l§c" . "UHC");

				if (!$server->isOnline()) {
					$item->setLore(
						[
							"§r§8Hardcore $br$br" .
							"§f· Cool scenarios (+??)$br" .
							"· Leaderboard Prize $br" .
							"· FFA & Teams games $br$br" .
							"§6#1 §7Top Kills: 4000 §6G-Coins$br$br" .
							"§c" . "This server is currently offline!"
						]
					);
					break;
				}

				if ($server->isWhitelist()) {
					$item->setLore(
						[
							"§r§8Hardcore $br$br" .
							"§f· Cool scenarios (+??)$br" .
							"· Leaderboard Prize $br" .
							"· FFA & Teams games $br$br" .
							"§6#1 §7Top Kills: 4000 §6G-Coins$br$br" .
							"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
							"§cWHITELISTED"
						]
					);
					break;
				}

				$item->setLore(
					[
						"§r§8Hardcore $br$br" .
						"§f· Cool scenarios (+??)$br" .
						"· Leaderboard Prize $br" .
						"· FFA & Teams games $br$br" .
						"§6#1 §7Top Kills: 4000 §6G-Coins$br$br" .
						"§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()} $br$br" .
						"§eClick to join to Practice."
					]
				);
				break;

			case "UHC_RUN":
				$item->setCustomName("§r§l§c" . "UHC Run");

				$item->setLore(
					[
						"§r§8Competitive $br$br" .
						"§cThis server is currently in development!"
					]
				);
				break;
		}

		return $item;
	}

	public function send(GhostlyPlayer $player): void
	{
		$this->getMenu()->send($player);
	}

	/**
	 * @return InvMenu
	 */
	public function getMenu(): InvMenu
	{
		return $this->menu;
	}
}