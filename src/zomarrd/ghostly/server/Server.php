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

use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\queries\SelectQuery;
use zomarrd\ghostly\mysql\queries\UpdateRowQuery;

final class Server
{
	public const PRACTICE = "Practice";
	public const COMBO = "Combo";
	public const UHC = "UHC";
	public const UHC_RUN = "UHC_RUN";
	public const HCF = "HCF";
	public const KITMAP = "KITMAP";

	public function __construct(
		private string      $server_name,
		private int         $players,
		private int         $max_players,
		private bool        $online,
		private bool        $whitelist,
		private null|string $category
	) {}

	/**
	 * @return string PLAYERS | WHITELISTED | OFFLINE
	 */
	public function getStatus(): string
	{
		if (!$this->isOnline()) {
			return '§r§c' . 'OFFLINE';
		}

		if ($this->isWhitelist()) {
			return '§r§c' . 'WHITELISTED';
		}

		return '§r§7' . 'Players: §f' . $this->getPlayers() . '§7/§f' . $this->getMaxPlayers();
	}

	public function isOnline(): bool
	{
		return $this->online;
	}

	public function isWhitelist(): bool
	{
		return $this->whitelist;
	}

	public function getPlayers(): int
	{
		return $this->players;
	}

	public function getMaxPlayers(): int
	{
		return $this->max_players;
	}

	public function sync_local(): void
	{
		$players = count(Ghostly::getInstance()->getServer()->getOnlinePlayers());
		$maxPlayers = Ghostly::getInstance()->getServer()->getMaxPlayers();
		$isWhitelist = Ghostly::getInstance()->getServer()->hasWhitelist() ? 1 : 0;
		MySQL::runAsync(new UpdateRowQuery(serialize(['players' => $players, 'max_players' => $maxPlayers, 'whitelist' => $isWhitelist]), 'server_name', $this->getName(), 'ghostly_servers'));
		$this->setMaxPlayers($maxPlayers);
		$this->setPlayers($players);
	}

	public function getName(): string
	{
		return $this->server_name;
	}

	public function setMaxPlayers(int $max_players): void
	{
		$this->max_players = $max_players;
	}

	public function setPlayers(int $players): void
	{
		$this->players = $players;
	}

	public function sync_remote(): void
	{
		MySQL::runAsync(new SelectQuery("SELECT * FROM ghostly_servers WHERE server_name='$this->server_name';"), function ($rows) {
			$row = $rows[0];
			if ($row !== null) {
				$this->setOnline((bool)$row['online']);
				$this->setPlayers((int)$row['players']);
				$this->setWhitelist((bool)$row['whitelist']);
				$this->setMaxPlayers((int)$row['max_players']);
				return;
			}

			$this->setOnline(false);
			$this->setPlayers(0);
		});
	}

	public function setOnline(bool $online): void
	{
		if (!$online) {
			MySQL::runAsync(new UpdateRowQuery(serialize(['online' => 0, 'players' => 0]), 'server_name', $this->getName(), 'ghostly_servers'));
		}

		$this->online = $online;
	}

	public function setWhitelist(bool $whitelist): void
	{
		$this->whitelist = $whitelist;
	}

	public function getCategory(): ?string
	{
		return $this->category;
	}

	public function setCategory(?string $category): void
	{
		$this->category = $category;
	}
}