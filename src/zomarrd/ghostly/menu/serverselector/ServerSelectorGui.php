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
use pocketmine\item\VanillaItems;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\server\Server;
use zomarrd\ghostly\server\ServerItems;
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

		$this->addButton(new MenuButton(ServerItems::get(Server::PRACTICE, VanillaItems::DIAMOND_SWORD()), function (GhostlyPlayer $player) use ($cooldown): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo(Server::PRACTICE);
			$cooldown[$player->getName()] = time();
		}), 12);

		$this->addButton(new MenuButton(ServerItems::get(Server::COMBO, VanillaItems::ENDER_PEARL()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo(Server::COMBO);
			$cooldown[$player->getName()] = time();
		}), 14);

		$this->addButton(new MenuButton(ServerItems::get(Server::HCF, VanillaItems::DIAMOND_PICKAXE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo(Server::HCF);
			$cooldown[$player->getName()] = time();
		}), 38);

		$this->addButton(new MenuButton(ServerItems::get(Server::KITMAP, VanillaItems::GOLDEN_PICKAXE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo(Server::KITMAP);
			$cooldown[$player->getName()] = time();
		}), 39);

		$this->addButton(new MenuButton(ServerItems::get(Server::UHC, VanillaItems::GOLDEN_APPLE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo(Server::UHC);
			$cooldown[$player->getName()] = time();
		}), 41);

		$this->addButton(new MenuButton(ServerItems::get(Server::UHC_RUN, VanillaItems::APPLE()), function (GhostlyPlayer $player): void {
			if (isset($cooldown[$player->getName()]) && time() - $cooldown[$player->getName()] < 1.5) {
				return;
			}

			$player->closeInventory();
			$player->transferTo(Server::UHC_RUN);
			$cooldown[$player->getName()] = time();
		}), 42);
	}

	/**
	 * @return InvMenu
	 */
	public function getMenu(): InvMenu
	{
		return $this->menu;
	}
}