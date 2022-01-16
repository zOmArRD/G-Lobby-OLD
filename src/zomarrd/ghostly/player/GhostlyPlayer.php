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

namespace zomarrd\ghostly\player;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\types\UIProfile;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\Sound;
use zomarrd\ghostly\extensions\scoreboard\Scoreboard;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\item\ItemManager;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\player\language\Language;
use zomarrd\ghostly\player\permission\PermissionKey;
use zomarrd\ghostly\server\Server;
use zomarrd\ghostly\server\ServerManager;
use zomarrd\ghostly\world\Lobby;

class GhostlyPlayer extends Player
{
	private string $currentLang = Language::ENGLISH_US;
	private bool $loaded = false;
	private Scoreboard $scoreboard_session;
	private ItemManager $itemManager;

	public function getUIProfile(): int
	{
		return DeviceData::getUIProfile($this->getName());
	}

	public function hasClassicProfile(): bool
	{
		return $this->getUIProfile() === UIProfile::CLASSIC;
	}

	public function hasDifferentLocale(): bool
	{
		return !$this->getLang(true)->equals($this->getLang());
	}

	public function getLang(bool $fromLocale = false): Language
	{
		$locale = $this->getLangHandler()->getLanguage($this->locale);
		return $fromLocale ? $locale : $this->getLangHandler()->getLanguage($this->currentLang);
	}

	public function getLangHandler(): LangHandler
	{
		return LangHandler::getInstance();
	}

	public function sendTranslated(string $string, array $replaceable = []): void
	{
		$this->sendMessage($this->getTranslation($string, $replaceable));
	}

	public function getTranslation(string $string, array $replaceable = []): string
	{
		return $this->getLang()->getMessage($string, $replaceable);
	}

	public function onUpdate(int $currentTick): bool
	{
		if (!$this->isLoaded()) {
			$this->setLanguage($this->locale);
			$this->scoreboard_session = new Scoreboard($this);
			$this->itemManager = new ItemManager($this);
			$this->setLoaded();
		}

		if ($currentTick % 10 === 0) {
			$this->getScoreboardSession()->setScoreboard();
		}

		return parent::onUpdate($currentTick);
	}

	public function isLoaded(): bool
	{
		return $this->loaded;
	}

	public function setLanguage(string $lang): void
	{
		$this->currentLang = $lang;
	}

	private function setLoaded(): void
	{
		$this->loaded = true;
	}

	public function getScoreboardSession(): Scoreboard
	{
		return $this->scoreboard_session;
	}

	public function onJoin(): void
	{
		$this->getLobbyItems();
		$this->setGamemode(GameMode::SURVIVAL());
		$this->setHealth(20);
		$this->getHungerManager()->setFood(20);
		$this->setAllowFlight(true);
		$this->setMovementSpeed($this->getMovementSpeed() * 1.2);
		$this->teleport_to_lobby();
	}

	public function getLobbyItems(): void
	{
		$this->getInventory()?->clearAll();
		foreach (['item-servers' => 0, 'item-cosmetics' => 4, 'item-lobby' => 8] as $item => $index) {
			$this->setItem($index, $this->getItemManager()->get($item));
		}
	}

	private function setItem(
		int  $index,
		Item $item
	): void
	{
		$this->getInventory()?->setItem($index, $item);
	}

	public function getItemManager(): ItemManager
	{
		return $this->itemManager;
	}

	public function isOp(): bool
	{
		return Ghostly::getInstance()->getServer()->isOp($this->getName());
	}

	/**
	 * @param string|Server $server
	 *
	 * @return void
	 * @todo Add Queue System for this shit omg!
	 */
	public function transferTo(string|Server $server): void
	{
		if (is_string($server)) {
			$server = ServerManager::getInstance()->getServerByName($server);
		}

		if (is_null($server)) {
			$this->broadcastSound(new ExplodeSound(), [$this]);
			$this->sendTranslated(LangKey::SERVER_CONNECT_ERROR_3);
			return;
		}

		if ($server->getName() === Ghostly::SERVER) {
			$this->broadcastSound(new AnvilUseSound(), [$this]);
			$this->sendTranslated(LangKey::SERVER_CONNECT_ERROR_1);
			return;
		}

		if (!$server->isOnline()) {
			$this->broadcastSound(new AnvilUseSound(), [$this]);
			$this->sendTranslated(LangKey::SERVER_NOT_ONLINE);
			return;
		}

		if ($server->isWhitelist() && !$this->hasPermission(PermissionKey::GHOSTLY_SERVER_CONNECT_WHITELISTED)) {
			$this->broadcastSound(new AnvilBreakSound(), [$this]);
			$this->sendTranslated(LangKey::SERVER_IS_WHITELISTED);
			return;
		}

		$this->sendTranslated(LangKey::SERVER_CONNECTING, ["{SERVER-NAME}" => $server->getName()]);
		$this->transfer($server->getName(), 0, "Transfer to {$server->getName()}");
	}

	public function teleport_to_lobby(): void
	{
		$lobby = Lobby::getInstance();

		if ($lobby !== null) {
			$this->teleport($lobby->getSpawnPosition(), $lobby->getSpawnYaw(), $lobby->getSpawnPitch());
		}
	}

	protected function internalSetGameMode(GameMode $gameMode) : void{
		$this->gamemode = $gameMode;

		$this->allowFlight = true;
		$this->hungerManager->setEnabled(false);

		if(!$this->isSpectator()) {
			if ($this->isSurvival()) {
				$this->setFlying(false);
			}
			$this->setSilent(false);
			$this->checkGroundState(0, 0, 0, 0, 0, 0);
		} else {
			$this->setFlying(true);
			$this->setSilent();
			$this->onGround = false;
			$this->sendPosition($this->location, null, null, MovePlayerPacket::MODE_TELEPORT);
		}
	}

	public function sendSound(Sound $sound): void
	{
		$this->broadcastSound($sound, [$this]);
	}
}