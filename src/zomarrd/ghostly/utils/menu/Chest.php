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

namespace zomarrd\ghostly\utils\menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\event\Listener;
use pocketmine\inventory\Inventory;
use zomarrd\ghostly\player\GhostlyPlayer;

abstract class Chest implements Listener
{
	private InvMenu $menu;

	/** @var array<int, MenuButton> */
	private array $buttons = [];

	/** @var callable[] */
	private array $inventoryClose = [];

	public function __construct(
		private string $chestName
	)
	{
		$this->menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST)->setName($this->chestName)
			->setInventoryCloseListener(function (GhostlyPlayer $player, Inventory $inventory): void {
				$closure = $this->inventoryClose[$player->getUniqueId()->toString()] ?? null;

				if ($closure === null) {
					return;
				}

				$closure($player);
				unset($this->inventoryClose[$player->getUniqueId()->toString()]);
			})->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
				$player = $transaction->getPlayer();
				$button = $this->buttons[$transaction->getAction()->getSlot()] ?? null;

				if ($button === null) {
					return $transaction->continue();
				}

				if ($player instanceof GhostlyPlayer) {
					$button->click($player);
				}
				return $transaction->discard();
			});
	}

	public function build(GhostlyPlayer $player): void
	{
		if (!$player->isOnline()) {
			return;
		}
		$this->menu->send($player);
	}

	public function addButton(MenuButton $button, int $slot): void
	{
		if ($slot >= $this->menu->getInventory()->getSize()) {
			return;
		}
		$this->menu->getInventory()->setItem($slot, $button->getItem());
		$this->buttons[$slot] = $button;
	}

	public function removeButton(): void
	{

	}

	protected function addCloseAction(GhostlyPlayer $player, callable $callable): void
	{
		$this->inventoryClose[$player->getUniqueId()->toString()] = $callable;
	}

	public function getMenu(): InvMenu
	{
		return $this->menu;
	}
}