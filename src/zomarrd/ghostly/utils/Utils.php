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

namespace zomarrd\ghostly\utils;

use pocketmine\utils\TextFormat;
use zomarrd\ghostly\server\ServerManager;

class Utils
{
	public static function checkStrings(string $string): string
	{
		$msg = $string;
		$toReplace = [
			"{BLUE}" => TextFormat::BLUE,
			"{GREEN}" => TextFormat::GREEN,
			"{RED}" => TextFormat::RED,
			"{DARK_RED}" => TextFormat::DARK_RED,
			"{PREFIX}" => PREFIX,
			"{DARK_BLUE}" => TextFormat::DARK_BLUE,
			"{DARK_AQUA}" => TextFormat::DARK_AQUA,
			"{DARK_GREEN}" => TextFormat::DARK_GREEN,
			"{GOLD}" => TextFormat::GOLD,
			"{GRAY}" => TextFormat::GRAY,
			"{DARK_GRAY}" => TextFormat::DARK_GRAY,
			"{DARK_PURPLE}" => TextFormat::DARK_PURPLE,
			"{LIGHT_PURPLE}" => TextFormat::LIGHT_PURPLE,
			"{RESET}" => TextFormat::RESET,
			"{YELLOW}" => TextFormat::YELLOW,
			"{AQUA}" => TextFormat::AQUA,
			"{BOLD}" => TextFormat::BOLD,
			"{WHITE}" => TextFormat::WHITE,
			"{date}" => date('d/m/y'),
			"{NETWORK.GET-PLAYERS}" => ServerManager::getInstance()->getNetworkPlayers(),
			"{NETWORK.GET-MAX_PLAYERS}" => ServerManager::getInstance()->getNetworkMaxPlayers()
		];
		$keys = array_keys($toReplace);
		$values = array_values($toReplace);
		for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
			$msg = str_replace($keys[$i], (string)$values[$i], $msg);
		}
		return $msg;
	}

	public static function stringifyKeys(array $array): \Generator
	{
		foreach ($array as $key => $value) {
			yield (string)$key => $value;
		}
	}
}