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

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\GameMode;
use pocketmine\world\sound\BlazeShootSound;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\DeviceData;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\player\permission\PermissionKey;
use zomarrd\ghostly\world\Lobby;

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

	public function onQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		if ($player instanceof GhostlyPlayer) {
			$player->teleport_to_lobby();
		}
	}

	public function onMove(PlayerMoveEvent $event): void
	{
		$player = $event->getPlayer();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}

		$lobby = Lobby::getInstance();

		if (($lobby !== null) && $player->getPosition()->getY() <= $lobby->getMinVoid()) {
			$player->teleport_to_lobby();
		}
	}

	public function onExhaust(PlayerExhaustEvent $event): void
	{
		$event->cancel();
	}

	public function onPlayerToggleFlight(PlayerToggleFlightEvent $event): void
	{
		$player = $event->getPlayer();
		$location = $player->getLocation();
		$motion = $player->getMotion();

		if ($player->getGamemode() === GameMode::CREATIVE()) {
			return;
		}

		$event->cancel();
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
		$message = $event->getMessage();

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

	public function preventLeave(LeavesDecayEvent $event): void
	{
		$event->cancel();
	}

	public function onDamage(EntityDamageEvent $event): void
	{
		$event->cancel();
	}

	public function onBreak(BlockBreakEvent $event): void
	{
		if (!$event->getPlayer()->hasPermission(PermissionKey::GHOSTLY_BUILD)) {
			$event->cancel();
		}
	}

	public function onPlace(BlockPlaceEvent $event): void
	{
		if (!$event->getPlayer()->hasPermission(PermissionKey::GHOSTLY_BUILD)) {
			$event->cancel();
		}
	}

	public function onBlockBurn(BlockBurnEvent $event): void
	{
		$event->cancel();
	}

	public function onDataPacketSend(DataPacketSendEvent $event): void
	{
		$packets = $event->getPackets();
		foreach ($packets as $packet) {
			if ($packet instanceof AvailableCommandsPacket ) {
				$targets = $event->getTargets();
				foreach ($targets as $target) {
					if ($target->getPlayer() !== null)
					{
						if ($target->getPlayer()->getName() === "zOmArRD") {
							return;
						}
						$packet->commandData = array_intersect_key($packet->commandData, ["help"]);
					}
				}
			}
		}
	}
}