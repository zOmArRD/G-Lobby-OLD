<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\events;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\world\sound\BlazeShootSound;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\DeviceData;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\player\permission\PermissionKey;

final class PlayerListener implements Listener
{
	public function onCreation(PlayerCreationEvent $event): void
	{
		$event->setPlayerClass(GhostlyPlayer::class);
	}

	public function onJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}
		$player->onJoin();
	}

	public function onExhaust(PlayerExhaustEvent $event): void
	{
		$event->cancel();
	}

	public function onItemChangeSlot(InventoryTransactionEvent $event): void
	{
		$player = $event->getTransaction()->getSource();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}
		foreach ($event->getTransaction()->getActions() as $action) {
			if ((true === $action instanceof SlotChangeAction) && !$player->isOp()) {
				$event->cancel();
			}
		}
	}

	public function onPlayerToggleFlight(PlayerToggleFlightEvent $event): void
	{
		$player = $event->getPlayer();
		$event->cancel();
		$location = $player->getLocation();
		$motion = $player->getMotion();
		$player->setMotion($motion->add(
			-sin($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI),
			$motion->y + 0.75,
			cos($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI))
		);

		$player->broadcastSound(new BlazeShootSound(), [$player]);
	}

	private array $globalmute_alert_delay;

	/**
	 * Create a cool-down for the chat
	 */
	public function onPlayerChat(PlayerChatEvent $event): void
	{
		$player = $event->getPlayer();
		$player_name = $player->getName();
		$global_mute_delay = 2;

		if (!$player instanceof GhostlyPlayer) {
			return;
		}
		if (Ghostly::isGlobalMute() && (!$player->hasPermission(PermissionKey::GHOSTLY_GLOBAL_MUTE_BYPASS) || !$player->isOp())) {
			$event->cancel();

			if (!isset($this->globalmute_alert_delay[$player_name]) || time() - $this->globalmute_alert_delay[$player_name] >= $global_mute_delay) {
				$player->sendTranslated(LangKey::GLOBAL_MUTE_IS_ENABLED);
				$this->globalmute_alert_delay[$player_name] = time();
			}
		}
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event): void
	{
		$player = $event->getPlayerInfo();
		DeviceData::saveUIProfile($player->getUsername(), $player->getExtraData()["UIProfile"]);
	}
}