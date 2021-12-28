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

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\language\Language;

class GhostlyPlayer extends Player
{
	private string $currentLang = Language::ENGLISH_US;

	public function getLang(bool $fromLocale = false): ?Language
	{
		$locale = $this->getLangHandler()->getLanguage($this->locale);

		if ($fromLocale) {
			return $locale ?? $this->getLangHandler()->getLanguage(Language::ENGLISH_US);
		}
		return $this->getLangHandler()->getLanguage($this->currentLang);
	}

	public function getLangHandler(): LangHandler
	{
		return LangHandler::getInstance();
	}

	public function hasDifferentLocale(): bool
	{
		return !$this->getLang(true)?->equals($this->getLang());
	}

	public function setLanguage(string $lang): void
	{
		$this->currentLang = $lang;
	}

}