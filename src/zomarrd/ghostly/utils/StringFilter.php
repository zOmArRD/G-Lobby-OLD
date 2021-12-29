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

class StringFilter
{
	public static function checkStrings(string $string): string
	{
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
			"{GET.NETWORK.PLAYERS}" => ServerManager::getInstance()?->getNetworkPlayers(),
			"{GET.NETWORK.MAX-PLAYERS}" => ServerManager::getInstance()?->getNetworkMaxPlayers()
		];
		$keys = array_keys($toReplace);
		$values = array_values($toReplace);
		for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
			$msg = str_replace($keys[$i], (string)$values[$i], $string);
		}
		return $msg ?? $string;
	}
}