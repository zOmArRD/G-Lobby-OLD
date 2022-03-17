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
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\server\ServerManager;

class Utils
{
    public const ONLY_PLAYER = PREFIX . "§cThis command must be executed by a player!";

    /**
     * @param string             $string
     * @param GhostlyPlayer|null $player
     *
     * @return string The formatted text, replaces custom variables with their respective values.
     */
    public static function checkStrings(string $string, ?GhostlyPlayer $player = null): string
    {
        $msg = $string;
        $toReplace = ["{BLUE}" => TextFormat::BLUE, "{GREEN}" => TextFormat::GREEN, "{RED}" => TextFormat::RED, "{DARK_RED}" => TextFormat::DARK_RED, "{PREFIX}" => PREFIX, "{DARK_BLUE}" => TextFormat::DARK_BLUE, "{DARK_AQUA}" => TextFormat::DARK_AQUA, "{DARK_GREEN}" => TextFormat::DARK_GREEN, "{GOLD}" => TextFormat::GOLD, "{GRAY}" => TextFormat::GRAY, "{DARK_GRAY}" => TextFormat::DARK_GRAY, "{DARK_PURPLE}" => TextFormat::DARK_PURPLE, "{LIGHT_PURPLE}" => TextFormat::LIGHT_PURPLE, "{RESET}" => TextFormat::RESET, "{YELLOW}" => TextFormat::YELLOW, "{AQUA}" => TextFormat::AQUA, "{BOLD}" => TextFormat::BOLD, "{WHITE}" => TextFormat::WHITE, "{date}" => date('d/m/y'), "{NETWORK.GET-PLAYERS}" => ServerManager::getInstance()->getNetworkPlayers(), "{NETWORK.GET-MAX_PLAYERS}" => ServerManager::getInstance()->getNetworkMaxPlayers(), "{POSITION}" => $player?->getQueue()?->getPositionFormatted(), "{SERVER-QUEUED}" => $player?->getQueue()?->getServer()];
        $keys = array_keys($toReplace);
        $values = array_values($toReplace);

        for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
            $msg = str_replace($keys[$i], (string)$values[$i], $msg);
        }

        return $msg;
    }

    /**
     * @param string $needle
     * @param string $haystack
     *
     * @return array
     */
    public static function str_indexes(string $needle, string $haystack): array
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

    public static function array_replace_values(array $array, string $replaceable, string $newText): array
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

    public static function arr_contains_value($needle, array $haystack, bool $strict = true): bool
    {
        return in_array($needle, $haystack, $strict);
    }

    public static function str_replace(string $haystack, array $values): string
    {
        $result = $haystack;
        $keys = array_keys($values);

        foreach ($keys as $value) {
            $value = (string)$value;
            $replaced = (string)$values[$value];
            if (self::str_contains($value, $haystack)) {
                $result = str_replace($value, $replaced, $result);
            }
        }

        return $result;
    }

    public static function str_contains(string $needle, string $haystack, bool $use_mb = false): bool
    {
        $result = false;
        $type = ($use_mb === true) ? mb_strpos($haystack, $needle) : strpos($haystack, $needle);

        if (is_bool($type)) {
            $result = $type;
        } elseif (is_int($type)) {
            $result = $type > -1;
        }

        return $result;
    }
}