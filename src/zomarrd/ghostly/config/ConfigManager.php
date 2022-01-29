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

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\WorldManager;
use RuntimeException;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\world\Lobby;

final class ConfigManager
{
	public static ConfigManager $instance;
	private static Config $server_config;

	private array $files = [
		'server_config.json' => 4.0
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
        if (!@mkdir($concurrentDirectory = $this->getDataFolder()) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

		foreach ($this->files as $file => $version) {
			$this->saveResource($file);
			$tempFile = $this->getFile($file);

			if ($tempFile->get('version') !== $version) {
				Ghostly::$logger->error("The $file aren't compatible with the current version, the old file are in " . $this->getDataFolder() . "$file.old");
				rename($this->getDataFolder() . $file, $this->getDataFolder() . $file . '.old');
				$this->saveResource($file, true);
			}

			unset($tempFile);
		}

		self::$server_config = $this->getFile('server_config.json');

		Ghostly::$is_proxy_server = self::getServerConfig()->get('is_proxy_server');

		define('PREFIX', self::getServerConfig()?->get('prefix'));
		define('MySQL', self::getServerConfig()?->get('mysql.credentials'));

		$data = self::$server_config->get("player-spawn");

		if (!$data["is_enabled"]) {
			return;
		}

		$levelName = $data["world"]["name"];

		if (!$this->getWorldManager()->isWorldLoaded($levelName)) {
			$this->getWorldManager()->loadWorld($levelName);
		}

		$lobby = new Lobby(
			Server::getInstance()->getWorldManager()->getWorldByName($levelName),
			$data["pos"]["x"],
			$data["pos"]["y"],
			$data["pos"]["z"],
			$data["pos"]["yaw"],
			$data["pos"]["pitch"],
			$data["world"]["min-void"]
		);

		$lobby->getWorld()->stopTime();
		$lobby->getWorld()->setTime(12800);
	}

	public function getDataFolder(): string
	{
		return Ghostly::getInstance()->getDataFolder();
	}

	public function getFile(string $file): Config
	{
		return new Config($this->getDataFolder() . $file);
	}

	public static function getServerConfig(): Config
	{
		return self::$server_config;
	}

	public function saveResource(string $file, bool $replace = false): void
	{
		Ghostly::getInstance()->saveResource($file, $replace);
	}

	public function getWorldManager(): WorldManager
	{
		return Server::getInstance()->getWorldManager();
	}
}