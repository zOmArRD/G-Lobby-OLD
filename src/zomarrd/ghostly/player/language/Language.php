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

namespace zomarrd\ghostly\player\language;

use pocketmine\utils\TextFormat;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\utils\Utils;

/**
 * @copyright GitHub Open Source lmao
 */
class Language
{
	public const ENGLISH_US = "en_US";

	/**
	 * @param string   $locale
	 * @param string[] $names
	 * @param string[] $messages
	 * @param string[] $item_names
	 * @param string   $authors
	 */
	public function __construct(
		private string $locale,
		private array  $names,
		private array  $messages,
		private array  $item_names,
		private string $authors
	){}

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
	public function hasName(string $name, string $locale = ""): bool
	{
		if ($locale !== "" && isset($this->names[$locale])) {
			$resultingName = $this->names[$locale];
			return $resultingName === $name;
		}
		$values = array_values($this->names);
		return in_array($name, $values);
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

	public function getMessage(string $type, array $replaceable = []): string
	{
		if (isset($this->messages[$type])) {
			$message = Utils::checkStrings($this->messages[$type]);
			foreach ($replaceable as $key => $value) {
				if (str_contains((string)$message, (string)$key)) {
					$message = str_replace($key, $value, $message);
				}
			}
			return $message;
		}
		return "";
	}

	public function getItemNames(string $type): ?string
	{
		if (isset($this->item_names[$type])) {
			return Utils::checkStrings($this->item_names[$type]);
		}
		return null;
	}

	public function hasAuthors(): bool
	{
		return $this->getAuthors() !== "";
	}

	public function getAuthors(): string
	{
		return $this->authors;
	}

	public static function openLangForm(GhostlyPlayer $player): LangForm
	{
		return new LangForm($player);
	}
}