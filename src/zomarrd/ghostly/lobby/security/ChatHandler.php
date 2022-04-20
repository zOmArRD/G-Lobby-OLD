<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 19/2/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\security;

use pocketmine\utils\Config;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\utils\Utils;

final class ChatHandler
{
    public static Config $filter;
    private static array $contents = [];

    public function __construct()
    {
        $contents = file(Ghostly::getInstance()->getResourcesFolder() . 'spam/bad-words.txt');
        self::$filter = new Config(Ghostly::getInstance()->getResourcesFolder() . 'spam/filter.yml');

        foreach ($contents as $content) {
            $content = strtolower(trim($content));
            self::$contents[$content] = true;
        }
    }

    public static function getUncensoredMessage(string $msg): string
    {
        $result = $msg;

        if (self::hasCensoredWords($msg)) {
            $words = self::getCensoredWordsIn($msg);
            $replacedWords = [];

            foreach ($words as $word) {
                $key = (string)$word;
                $val = '';
                $replacedWords[$key] = $val;
            }

            $result = Utils::str_replace($result, $replacedWords);
        }

        return $result;
    }

    public static function hasCensoredWords(string $msg): bool
    {
        $censoredWords = self::getCensoredWordsIn($msg);
        return count($censoredWords) > 0;
    }

    public static function getCensoredWordsIn(string $msg): array
    {
        $result = [];
        $lowerCaseMsg = strtolower($msg);
        $words = explode(' ', $lowerCaseMsg);

        foreach ($words as $word) {
            $lowerCaseWord = strtolower($word);

            if (isset(self::$contents[$lowerCaseWord])) {
                $len = strlen($lowerCaseWord);
                $indexes = Utils::str_indexes($lowerCaseWord, $lowerCaseMsg);

                foreach ($indexes as $index) {
                    $str = substr($msg, $index, $len);

                    if (!Utils::arr_contains_value($str, $result)) {
                        $result[] = $str;
                    }
                }
            }
        }

        return $result;
    }
}