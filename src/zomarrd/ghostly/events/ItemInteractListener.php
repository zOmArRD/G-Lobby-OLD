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

namespace zomarrd\ghostly\events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use zomarrd\ghostly\menu\Menu;
use zomarrd\ghostly\player\GhostlyPlayer;

final class ItemInteractListener implements Listener
{
	private array $item_cooldown;

	public function legacyInteract(PlayerItemUseEvent $event): void
	{
		$player = $event->getPlayer();
		$pn = $player->getName();

		if (!$player instanceof GhostlyPlayer) {
			return;
		}
		$item = $event->getItem();
		$itemManager = $player->getItemManager();

		if (!isset($this->item_cooldown[$pn]) || time() - $this->item_cooldown[$pn] >= 1.5) {
			switch (true) {
				case $item->equals($itemManager->get('lobby-selector')):
					if ($player->hasClassicProfile()) {
						Menu::LOBBY_SELECTOR_GUI()->build($player);
					} else {
						/*TODO: Send Form Menu*/
					}
					break;
			}
			$this->item_cooldown[$pn] = time();
		}
	}
}