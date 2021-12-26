<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 24/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\config;

use pocketmine\utils\Config;
use zomarrd\ghostly\Ghostly;

final class ConfigManager
{
	public static ConfigManager $instance;
	private static ?Config $server_config;
	private array $files = [
		'server_config.json' => 1.5
	];

	public function __construct()
	{
		self::$instance = $this;
		$this->init();
	}

	public static function getInstance(): ConfigManager
	{
		return self::$instance;
	}

	public function init(): void
	{
		/** This can be erased? */
		@mkdir($this->getDataFolder());

		foreach ($this->files as $file => $version) {
			$this->saveResource($file);
			$tempFile = self::getFile($file);
			if ($tempFile->get('version') !== $version) {
				Ghostly::$logger->error("The {$file} aren't compatible with the current version, the old file are in " . $this->getDataFolder() . "{$file}.old");
				rename($this->getDataFolder() . $file, $this->getDataFolder() . $file . '.old');
				$this->saveResource($file, true);
			}
			unset($tempFile);
		}
		self::$server_config = $this->getFile('server_config.json');
		define('PREFIX', self::getServerConfig()->get('prefix'));
		define('MySQL', self::getServerConfig()->get('mysql.credentials'));
	}

	public function getDataFolder(): string
	{
		return Ghostly::getInstance()->getDataFolder();
	}

	public function getFile(string $file): Config
	{
		return new Config(self::getDataFolder() . $file);
	}

	public static function getServerConfig(): ?Config
	{
		return self::$server_config;
	}

	public function saveResource(string $file, bool $replace = false): void
	{
		Ghostly::getInstance()->saveResource($file, $replace);
	}
}