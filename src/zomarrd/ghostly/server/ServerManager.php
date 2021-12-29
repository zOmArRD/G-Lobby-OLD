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

namespace zomarrd\ghostly\server;

use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use zomarrd\ghostly\config\ConfigManager;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\queries\RegisterServerQuery;
use zomarrd\ghostly\mysql\queries\SelectQuery;

final class ServerManager
{
	private static ?ServerManager $instance = null;

	private ?Server $current_server = null;

	/** @var Server[] */
	private array $servers = [];

	public function __construct()
	{
		self::$instance = $this;
		$this->init();
	}

	public static function getInstance(): ?ServerManager
	{
		return self::$instance;
	}

	public function getCurrentServer(): ?Server
	{
		return $this->current_server;
	}

	public function getServers(): array
	{
		return $this->servers;
	}

	public function init(): void
	{
		if (!$this->getConfig()->get('current.server')['is.enabled']) {
			return;
		}
		$cServerName = $this->getConfig()->get('current.server')['server.name'];
		Ghostly::$logger->info(PREFIX . 'Registering the server in the database');
		MySQL::runAsync(new RegisterServerQuery($cServerName));
		sleep(1); //WHY YES ?
		$this->reloadServers();

		Ghostly::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			$this->getCurrentServer()?->sync();
			foreach ($this->getServers() as $server) {
				$server->sync(false);
			}
		}), 60);
	}

	private function getConfig(): Config
	{
		return ConfigManager::getServerConfig();
	}

	public function reloadServers(): void
	{
		$this->servers = [];
		$cServerName = $this->getConfig()->get('current.server')['server.name'];
		MySQL::runAsync(new SelectQuery('SELECT * FROM network_servers;'), 'network_servers', function ($rows) use ($cServerName) {
			foreach ($rows as $row) {
				$server = new Server($row['server_name'], (int)$row['players'], (int)$row['max_players'], (bool)$row['online'], (bool)$row['whitelist']);
				if ($row['server_name'] === $cServerName) {
					$this->current_server = $server;
				} else {
					$this->servers[] = $server;
				}
				Ghostly::$logger->info(PREFIX . "The server ({$server->getServerName()}) has been registered in the database!");
			}
		});
	}

	public function getServerByName(string $name): ?Server
	{
		foreach ($this->getServers() as $server) {
			return ($server->getServerName() === $name) ? $server : null;
		}
		return null;
	}

	/**
	 * Function dedicated to the Scoreboard :)
	 *
	 * @return int
	 */
	public function getNetworkPlayers(): int
	{
		$players = 0;
		foreach ($this->getServers() as $server) {
			$players += $server->getPlayers();
		}
		$players += count(Ghostly::getInstance()->getServer()->getOnlinePlayers());
		return $players;
	}

	public function getNetworkMaxPlayers(): int
	{
		$maxPlayers = Ghostly::getInstance()->getServer()->getMaxPlayers();
		foreach ($this->getServers() as $server) {
			$maxPlayers += $server->getMaxPlayers();
		}
		return $maxPlayers;
	}
}