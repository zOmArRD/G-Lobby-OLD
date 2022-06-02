<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 21/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\LangHandler;
use zomarrd\ghostly\lobby\player\language\Language;
use zomarrd\ghostly\lobby\server\ServerManager;

const ONLY_PLAYERS = '§cOnly players can use this command.';


/// Strings functions.

function strContains(string $needle, string $haystack, bool $use_mb = false): bool
{
    $result = false;
    $type = ($use_mb === true) ? mb_strpos($haystack, $needle) : strpos($haystack, $needle);

    if (is_bool($type)) {
        $result = $type;
    } else if (is_int($type)) {
        $result = $type > -1;
    }

    return $result;
}

function strReplace(string $haystack, array $values): string
{
    $result = $haystack;
    $keys = array_keys($values);

    foreach ($keys as $value) {
        $value = (string)$value;
        $replaced = (string)$values[$value];
        if (strContains($value, $haystack)) {
            $result = str_replace($value, $replaced, $result);
        }
    }

    return $result;
}

function arrContainsValue(mixed $needle, array $haystack, bool $strict = true): bool
{
    return in_array($needle, $haystack, $strict);
}

function arrayReplaceValues(array $array, string $replaceable, string $newText): array
{
    $new = [];
    foreach ($array as $key => $value) {
        if ($value === $replaceable) {
            $value = $newText;
        }
        $new[$key] = $value;
    }

    return $new;
}

function strIndexes(string $needle, string $haystack): array
{

    $result = [];

    $end = strlen($needle);

    $len = 0;

    while (($len + $end) <= strlen($haystack)) {

        $substr = substr($haystack, $len, $end);

        if ($needle === $substr) {
            $result[] = $len;
        }

        $len++;
    }

    return $result;
}

function checkStrings(string $string, ?GhostlyPlayer $player = null): string
{
    $msg = $string;
    $toReplace = [
        '{BLUE}' => TextFormat::BLUE,
        '{GREEN}' => TextFormat::GREEN,
        '{RED}' => TextFormat::RED,
        '{DARK_RED}' => TextFormat::DARK_RED,
        '{PREFIX}' => PREFIX,
        '{DARK_BLUE}' => TextFormat::DARK_BLUE,
        '{DARK_AQUA}' => TextFormat::DARK_AQUA,
        '{DARK_GREEN}' => TextFormat::DARK_GREEN,
        '{GOLD}' => TextFormat::GOLD,
        '{GRAY}' => TextFormat::GRAY,
        '{DARK_GRAY}' => TextFormat::DARK_GRAY,
        '{DARK_PURPLE}' => TextFormat::DARK_PURPLE,
        '{LIGHT_PURPLE}' => TextFormat::LIGHT_PURPLE,
        '{RESET}' => TextFormat::RESET,
        '{YELLOW}' => TextFormat::YELLOW,
        '{AQUA}' => TextFormat::AQUA,
        '{BOLD}' => TextFormat::BOLD,
        '{WHITE}' => TextFormat::WHITE,
        '{date}' => date('d/m/y'),
        '{NETWORK.GET-PLAYERS}' => ServerManager::getInstance()->getNetworkPlayers(),
        '{NETWORK.GET-MAX_PLAYERS}' => ServerManager::getInstance()->getNetworkMaxPlayers(),
        '{PLAYER.QUEUE-POSITION}' => $player?->getQueue()?->getPositionFormatted(),
        '{PLAYER.QUEUE-SERVER}' => $player?->getQueue()?->getServer(),
        '{PLAYER.G-COINS}' => $player?->getBalanceAPI()?->getBalance()
    ];
    $keys = array_keys($toReplace);
    $values = array_values($toReplace);

    for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
        $msg = str_replace($keys[$i], (string)$values[$i], $msg);
    }

    return $msg;
}

/// Random functions.

/**
 * Try to locate a player by name.
 *
 * @param string $name
 *
 * @return Player|OfflinePlayer
 */
function findPlayer(string $name): Player|OfflinePlayer
{
    $player = Server::getInstance()->getPlayerByPrefix($name);

    if (!isset($player)) {
        $player = Server::getInstance()->getOfflinePlayer($name);
    }

    return $player;
}

//// Language functions

function getLanguages(): array
{
    return LangHandler::getInstance()->getLanguages();
}

function getLanguage(string $lang): Language
{
    return LangHandler::getInstance()->getLanguage($lang);
}