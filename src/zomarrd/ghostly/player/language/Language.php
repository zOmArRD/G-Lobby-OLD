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
		if ($locale !== "" and isset($this->names[$locale])) {
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

	public function getMessage(string $type, array $replaceable = [])
	{
		if (isset($this->messages[$type])) {
			$message = $this->convertString($this->messages[$type]);
			foreach ($replaceable as $key => $value) {
				$search = "{{$key}}";
				if (str_contains($message, $search)) {
					$message = str_replace($search, $value, $message);
				}
			}
			return $message;
		}
		return null;
	}

	public function convertString(string $string): string
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
			"{WHITE}" => TextFormat::WHITE
		];
		foreach ($toReplace as $search => $replace) {
			if (str_contains($string, $search)) {
				$string = str_replace($search, $replace, $string);
			}
		}
		return $string;
	}

	public function getItemNames(string $type): ?string
	{
		if (isset($this->item_names[$type])) {
			return $this->convertString($this->item_names[$type]);
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
}