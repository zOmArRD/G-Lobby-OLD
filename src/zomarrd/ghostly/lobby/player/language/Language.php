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

namespace zomarrd\ghostly\lobby\player\language;

use zomarrd\ghostly\lobby\player\GhostlyPlayer;

/**
 * @copyright GitHub Open Source lmao
 */
final class Language
{
    public const ENGLISH_US = 'en_US';

    /**
     * @param string $locale
     * @param array  $names
     * @param array  $strings
     */
    public function __construct(private string $locale, private array $names, private array $strings) {}

    public static function openLangForm(GhostlyPlayer $player): LangForm
    {
        return new LangForm($player);
    }

    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @return string Gets the name based on the locale.
     */
    public function getNameFromLocale(string $locale = self::ENGLISH_US): string
    {
        return $this->names[$locale] ?? $this->getName();
    }

    public function getName(): string
    {
        return $this->names[$this->locale];
    }

    /**
     * @return bool Determines if the language contains the name. Can be strict based on locale.
     */
    public function hasName(string $name, string $locale = ''): bool
    {
        if ($locale === '' || !isset($this->names[$locale])) {
            $values = array_values($this->names);
            return in_array($name, $values, true);
        }

        $resultingName = $this->names[$locale];
        return $resultingName === $name;
    }

    public function equals(Language $lang): bool
    {
        return $this->locale === $lang->getLocale();
    }

    /**
     * @return string Gets the locale of the lang.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getStrings(string $type, array $replaceable = []): string
    {
        if (!isset($this->strings[$type])) {
            return '';
        }

        $message = checkStrings($this->strings[$type]);

        foreach ($replaceable as $key => $value) {
            if (str_contains((string)$message, (string)$key)) {
                $message = str_replace($key, $value, $message);
            }
        }

        return $message;
    }
}