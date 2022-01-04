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
use pocketmine\network\mcpe\protocol\types\UIProfile;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use zomarrd\ghostly\extensions\scoreboard\Scoreboard;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\item\ItemManager;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\language\Language;

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
		if ($fromLocale) {
			return $locale;
		}
		return $this->getLangHandler()->getLanguage($this->currentLang);
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
			return parent::onUpdate($currentTick);
		}
		if ($currentTick % 20 === 0) {
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
		$this->setGamemode(GameMode::ADVENTURE());
		$this->setHealth(20);
		$this->getHungerManager()->setFood(20);
		$this->setAllowFlight(true);
		$this->setMovementSpeed($this->getMovementSpeed() * 1.5);
		/*TODO: Spawn on the lobby*/
	}

	public function getLobbyItems(): void
	{
		$this->getInventory()?->clearAll();
		foreach (['server-selector' => 0, 'lobby-selector' => 8] as $item => $index) {
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

}