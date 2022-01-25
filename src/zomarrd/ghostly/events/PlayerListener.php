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

use pocketmine\block\BlockLegacyIds;
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
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\GameMode;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\queries\InsertQuery;
use zomarrd\ghostly\mysql\queries\SelectQuery;
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

	/**
	 * @param \pocketmine\event\player\PlayerPreLoginEvent $event
	 *
	 * @return void
	 * @todo Move this to the login packet
	 */
	public function PlayerPreLoginEvent(PlayerPreLoginEvent $event): void
	{
		$playerInfo = $event->getPlayerInfo();
		$name = $playerInfo->getUsername();
		$locale = $playerInfo->getLocale();

		DeviceData::saveUIProfile($playerInfo->getUsername(), $playerInfo->getExtraData()["UIProfile"]);

		MySQL::runAsync(new SelectQuery("SELECT * FROM player_config WHERE player = '$name';"), static function ($result) use ($name, $locale): void {
			if(count($result) === 0) {
				MySQL::runAsync(new InsertQuery("INSERT INTO player_config(player, lang, scoreboard) VALUES ('$name', '$locale', true);"));
			}
		});
	}

	public function PlayerLoginEvent(PlayerLoginEvent $event): void
	{
		$player = $event->getPlayer();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}

		$player_name = $player->getName();

		MySQL::runAsync(new SelectQuery("SELECT * FROM player_config WHERE player = '$player_name';"), static function ($result) use ($player, $player_name): void {
			if (count($result) === 0) {
				$player->transfer("ghostlymc.live");
				return;
			}
			$data = $result[0];

			$player->setLanguage($data->lang);
			$player->setScoreboard((bool)$data->scoreboard);
		});
	}

	public function PlayerJoinEvent(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();

		if ($player instanceof GhostlyPlayer) {
			$player->onJoin();
		}
	}

	public function PlayerQuitEvent(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		if ($player instanceof GhostlyPlayer) {
			$player->teleport_to_lobby();
		}
	}

	public function PlayerMoveEvent(PlayerMoveEvent $event): void
	{
		$player = $event->getPlayer();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}

		$block = $player->getWorld()->getBlock($player->getLocation()->subtract(0, -1, 0));

		/*if ($block->getId() === ItemIds::WATER) {
			$player->getNetworkProperties()->setPlayerFlag( EntityMetadataFlags::SWIMMING, true);
		} else {
			$player->getNetworkProperties()->setPlayerFlag( EntityMetadataFlags::SWIMMING, false);
		}*/

		$lobby = Lobby::getInstance();

		if (is_null($lobby)) {
			return;
		}

		$motion = $player->getMotion();
		$location = $player->getLocation();

		if ($lobby->getWorld()->getBlock($player->getLocation()->floor()->subtract(0, 1, 0))->getId() === BlockLegacyIds::SLIME) {
			$x = -sin($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI);
			$z = cos($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI);
			$player->setMotion($motion->add($x * 2, 1.20, $z * 2));
			$player->sendSound(LevelSoundEvent::LAUNCH);
		}

		if ($location->getY() <= $lobby->getMinVoid()) {
			$player->teleport_to_lobby();
		}
	}

	public function PlayerExhaustEvent(PlayerExhaustEvent $event): void
	{
		$event->cancel();
	}

	public function PlayerToggleFlightEvent(PlayerToggleFlightEvent $event): void
	{
		$player = $event->getPlayer();
		$location = $player->getLocation();
		$motion = $player->getMotion();
		if (!$player instanceof GhostlyPlayer) {
			return;
		}

		if ($player->getGamemode() === GameMode::CREATIVE()) {
			return;
		}

		$event->cancel();
		$player->setMotion($motion->add(
			-sin($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI),
			$motion->y + 0.75,
			cos($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI))
		);

		$player->sendSound(LevelEvent::SOUND_BLAZE_SHOOT, "level-event");
	}

	private array $globalmute_alert_delay;

	/**
	 * Create a cool-down for the chat
	 */
	public function PlayerChatEvent(PlayerChatEvent $event): void
	{
		$player = $event->getPlayer();
		$player_name = $player->getName();
		$global_mute_delay = 2;
		$message = $event->getMessage();

		if (!$player instanceof GhostlyPlayer) {
			return;
		}

		/* global mute stuff */
		if (!Ghostly::isGlobalMute() || ($player->hasPermission(PermissionKey::GHOSTLY_GLOBAL_MUTE_BYPASS) && $player->isOp())) {
			return;
		}

		$event->cancel();

		if (!isset($this->globalmute_alert_delay[$player_name]) || time() - $this->globalmute_alert_delay[$player_name] >= $global_mute_delay) {
			$player->sendTranslated(LangKey::GLOBAL_MUTE_IS_ENABLED);
			$this->globalmute_alert_delay[$player_name] = time();
		}
		/** end */
	}

	public function LeavesDecayEvent(LeavesDecayEvent $event): void
	{
		$event->cancel();
	}

	public function EntityDamageEvent(EntityDamageEvent $event): void
	{
		$event->cancel();
	}

	public function BlockBreakEvent(BlockBreakEvent $event): void
	{
		if ($event->getPlayer()->hasPermission(PermissionKey::GHOSTLY_BUILD)) {
			return;
		}
		$event->cancel();
	}

	public function BlockPlaceEvent(BlockPlaceEvent $event): void
	{
		if ($event->getPlayer()->hasPermission(PermissionKey::GHOSTLY_BUILD)) {
			return;
		}
		$event->cancel();
	}

	public function BlockBurnEvent(BlockBurnEvent $event): void
	{
		$event->cancel();
	}

	public function DataPacketSendEvent(DataPacketSendEvent $event): void
	{
		$packets = $event->getPackets();
		foreach ($packets as $packet) {
			if (!$packet instanceof AvailableCommandsPacket) {
				continue;
			}

			$targets = $event->getTargets();
			foreach ($targets as $target) {
				if ($target->getPlayer() !== null && $target->getPlayer()->getName() !== "zOmArRD") {
					$packet->commandData = array_intersect_key($packet->commandData, ["help"]);
				}
			}
		}
	}
}