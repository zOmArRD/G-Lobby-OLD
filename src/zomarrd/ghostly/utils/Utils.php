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
    public const ONLY_PLAYER = PREFIX . "§cThis command must be executed by a player!";

    public static function checkStrings(string $string): string
    {
        $msg = $string;
        $toReplace = ["{BLUE}" => TextFormat::BLUE, "{GREEN}" => TextFormat::GREEN, "{RED}" => TextFormat::RED, "{DARK_RED}" => TextFormat::DARK_RED, "{PREFIX}" => PREFIX, "{DARK_BLUE}" => TextFormat::DARK_BLUE, "{DARK_AQUA}" => TextFormat::DARK_AQUA, "{DARK_GREEN}" => TextFormat::DARK_GREEN, "{GOLD}" => TextFormat::GOLD, "{GRAY}" => TextFormat::GRAY, "{DARK_GRAY}" => TextFormat::DARK_GRAY, "{DARK_PURPLE}" => TextFormat::DARK_PURPLE, "{LIGHT_PURPLE}" => TextFormat::LIGHT_PURPLE, "{RESET}" => TextFormat::RESET, "{YELLOW}" => TextFormat::YELLOW, "{AQUA}" => TextFormat::AQUA, "{BOLD}" => TextFormat::BOLD, "{WHITE}" => TextFormat::WHITE, "{date}" => date('d/m/y'), "{NETWORK.GET-PLAYERS}" => ServerManager::getInstance()->getNetworkPlayers(), "{NETWORK.GET-MAX_PLAYERS}" => ServerManager::getInstance()->getNetworkMaxPlayers()];
        $keys = array_keys($toReplace);
        $values = array_values($toReplace);

        for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
            $msg = str_replace($keys[$i], (string)$values[$i], $msg);
        }

        return $msg;
    }

    public static function str_indexOf(string $needle, string $haystack, int $len = 0): int
    {

        $result = -1;

        $indexes = self::str_indexes($needle, $haystack);

        $length = count($indexes);

        if ($length > 0) {

            --$length;

            $indexOfArr = ($len > $length or max($len, 0));

            $result = $indexes[$indexOfArr];

        }

        return $result;
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

    public static function str_contains_vals(string $haystack, string...$needles): bool
    {
        $result = true;
        $size = count($needles);

        if ($size > 0) {
            foreach ($needles as $needle) {
                if (!self::str_contains($needle, $haystack)) {
                    $result = false;
                    break;
                }
            }
        } else {
            $result = false;
        }


        return $result;
    }

    public static function str_contains(string $needle, string $haystack, bool $use_mb = false): bool
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

    public static function arr_indexOf($needle, array $haystack, bool $strict = false): bool|int|string
    {
        $index = array_search($needle, $haystack, $strict);

        if (is_bool($index) && $index === false) {
            $index = -1;
        }

        return $index;
    }

    public static function arr_contains_keys(array $haystack, ...$needles): bool
    {
        $result = true;

        foreach ($needles as $key) {
            if (!isset($haystack[$key])) {
                $result = false;
                break;
            }
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

    public static function equals_string(string $input, string...$tests): bool
    {
        $result = false;

        foreach ($tests as $test) {
            if ($test === $input) {
                $result = true;
                break;
            }
        }

        return $result;
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

    public static function sort_array(array $arr): array
    {
        if (count($arr) === 1) {
            return $arr;
        }

        $middle = (int)(count($arr) / 2);
        $left = array_slice($arr, 0, $middle, true);
        $right = array_slice($arr, $middle, null, true);
        $left = self::sort_array($left);
        $right = self::sort_array($right);

        return self::merge($left, $right);
    }

    private static function merge(array $arr1, array $arr2): array
    {
        $result = [];

        while (count($arr1) > 0 and count($arr2) > 0) {
            $leftKey = array_keys($arr1)[0];
            $rightKey = array_keys($arr2)[0];
            $leftVal = $arr1[$leftKey];
            $rightVal = $arr2[$rightKey];
            if ($leftVal > $rightVal) {
                $result[$rightKey] = $rightVal;
                $arr2 = array_slice($arr2, 1, null, true);
            } else {
                $result[$leftKey] = $leftVal;
                $arr1 = array_slice($arr1, 1, null, true);
            }
        }

        while (count($arr1) > 0) {
            $leftKey = array_keys($arr1)[0];
            $leftVal = $arr1[$leftKey];
            $result[$leftKey] = $leftVal;
            $arr1 = array_slice($arr1, 1, null, true);
        }

        while (count($arr2) > 0) {
            $rightKey = array_keys($arr2)[0];
            $rightVal = $arr2[$rightKey];
            $result[$rightKey] = $rightVal;
            $arr2 = array_slice($arr2, 1, null, true);
        }

        return $result;
    }

    public static function canParse($s, bool $isInteger): bool
    {
        if (is_string($s)) {

            $abc = 'ABCDEFGHIJKLMNOPQRZTUVWXYZ';
            $invalid = $abc . strtoupper($abc) . "!@#$%^&*()_+={}[]|:;\"',<>?/";

            if ($isInteger === true) {
                $invalid .= '.';
            }

            $strArr = str_split($invalid);
            $canParse = self::str_contains_from_arr($s, $strArr);

        } else {
            $canParse = ($isInteger === true) ? is_int($s) : is_float($s);
        }

        return $canParse;
    }

    public static function str_contains_from_arr(string $haystack, array $needles): bool
    {
        $result = true;
        $size = count($needles);

        if ($size > 0) {
            foreach ($needles as $needle) {
                if (!self::str_contains($needle, $haystack)) {
                    $result = false;
                    break;
                }
            }
        } else {
            $result = false;
        }

        return $result;
    }
}