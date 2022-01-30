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
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\queries\RegisterServerQuery;
use zomarrd\ghostly\mysql\queries\SelectQuery;
use zomarrd\ghostly\player\GhostlyPlayer;

final class ServerManager
{
	private static ServerManager $instance;

	private ?Server $current_server = null;

	/** @var array<Server> */
	private array $servers = [];

	public function __construct()
	{
		self::$instance = $this;
		$this->init();
	}

	public function init(): void
	{
		$cServerName = $this->getCurrentServerName();

		Ghostly::$logger->info(PREFIX . 'Registering the server in the database');
		MySQL::runAsync(new RegisterServerQuery($cServerName));

		sleep(1); //WHY YES ?
		$this->reloadServers();

		Ghostly::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			$this->getCurrentServer()?->sync_local();
			foreach ($this->getServers() as $server) {
				$server->sync_remote();
			}
		}), 60);
	}

	public function getCurrentServerName(): string
	{
		return Ghostly::SERVER;
	}

	public function reloadServers(GhostlyPlayer $player = null): void
	{
		$this->servers = [];
		$cServerName = $this->getCurrentServerName();

		MySQL::runAsync(new SelectQuery("SELECT * FROM ghostly_servers"),
			function ($rows) use ($cServerName, $player) {
				foreach ($rows as $row) {
					$server = new Server($row['server_name'], (int)$row['players'], (int)$row['max_players'], (bool)$row['online'], (bool)$row['whitelist'], $row["category"]);

					if ($row['server_name'] === $cServerName) {
						$this->current_server = $server;
					} else {
						$this->servers[] = $server;
					}

					Ghostly::$logger->info(PREFIX . "The server ({$server->getName()}) has been registered in the database!");
					$player?->sendMessage(PREFIX . "The server ({$server->getName()}) has been registered in the database!");
				}
			});
	}

	public function getCurrentServer(): ?Server
	{
		return $this->current_server;
	}

	public function getServers(): array
	{
		return $this->servers;
	}

	public static function getInstance(): ServerManager
	{
		return self::$instance;
	}

	public function getServerByName(string $name): ?Server
	{
		foreach ($this->getServers() as $server) {
			if ($server->getName() !== $name) {
				continue;
			}

			return $server;
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
			if (!$server->isOnline()) {
				continue;
			}

			$maxPlayers += $server->getMaxPlayers();
		}

		return $maxPlayers;
	}
}