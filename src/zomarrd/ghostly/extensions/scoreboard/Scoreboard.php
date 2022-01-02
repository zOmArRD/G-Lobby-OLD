<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\extensions\scoreboard;

use pocketmine\utils\Config;
use zomarrd\ghostly\config\ConfigManager;
use zomarrd\ghostly\utils\Utils;

class Scoreboard extends ScoreAPI
{
	/** @var string[] This is to replace blanks */
	private const EMPTY_CACHE = ["§0\e", "§1\e", "§2\e", "§3\e", "§4\e", "§5\e", "§6\e", "§7\e", "§8\e", "§9\e", "§a\e", "§b\e", "§c\e", "§d\e", "§e\e"];

	public function setScoreboard(): void
	{
		$this->new('ghostly.lobby', $this->getConfig()['display']);
		$this->updateScoreboard();
	}

	private function updateScoreboard(): void
	{
		foreach ($this->getConfig()['lines'] as $line => $string) {
			$msg = $this->replaceData($line, (string)$string);
			$this->setLine($line, $msg);
		}
	}

	private function getConfig()
	{
		return ConfigManager::getServerConfig()?->get('scoreboard');
	}

	public function replaceData(int $line, string $string): string
	{
		if (empty($string)) {
			return self::EMPTY_CACHE[$line] ?? '';
		}
		return Utils::checkStrings($string);
	}
}