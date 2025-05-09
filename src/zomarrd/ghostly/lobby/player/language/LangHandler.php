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

use JsonException;
use zomarrd\ghostly\lobby\Ghostly;

/**
 * @copyright GitHub Open Source lmao
 */
final class LangHandler
{
    private static LangHandler $instance;

    /** @var array<Language>|null */
    private array $languages;

    private Language $defaultLanguage;

    /**
     * @throws JsonException
     */
    public function __construct()
    {
        self::$instance = $this;
        $resourcesFolder = Ghostly::getInstance()->getResourcesFolder() . 'lang';
        $files = scandir($resourcesFolder);

        foreach ($files as $file) {
            if (!str_contains($file, '.json')) {
                continue;
            }

            $path = $resourcesFolder . "/$file";
            $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $languageData = $data['language_data'];
            $default = $languageData['default'];
            $locale = str_replace('.json', '', $file);

            $lang = new Language($locale, $languageData['names'], array_merge(
                $data['player-strings'],
                $data['form-strings'],
                $data['language-strings'],
                $data['network-strings'],
                $data['queue-strings'],
                $data['item-strings']
            ));

            $this->languages[$locale] = $lang;

            if (!$default) {
                continue;
            }

            $this->defaultLanguage = $lang;
        }
    }

    public static function getInstance(): LangHandler
    {
        return self::$instance;
    }

    public function getLanguage(string $lang): Language
    {
        return $this->languages[$lang] ?? $this->defaultLanguage;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getLanguageFromName(string $name, string $locale = ''): ?Language
    {
        foreach ($this->languages as $language) {
            if (!$language->hasName($name, $locale)) {
                continue;
            }

            return $language;
        }

        return null;
    }
}