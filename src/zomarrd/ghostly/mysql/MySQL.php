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

namespace zomarrd\ghostly\mysql;

use pocketmine\Server;
use zomarrd\ghostly\mysql\queries\InsertQuery;

final class MySQL
{
	public const TABLE_PREFIX = "ghostly_";

	private static array $callbacks = [];

	private const CREATE_TABLE_SERVERS = "CREATE TABLE IF NOT EXISTS " . self::TABLE_PREFIX . "servers(server_name VARCHAR(16), players INT, max_players INT, online BOOLEAN, whitelist BOOLEAN, category VARCHAR(16));";
	private const CREATE_PLAYER_CONFIG = "CREATE TABLE IF NOT EXISTS player_config(player VARCHAR(16), lang VARCHAR(12), scoreboard BOOLEAN DEFAULT true);";
	private const CREATE_PLAYER_LOCATION = "CREATE TABLE IF NOT EXISTS player_location(xuid VARCHAR(50), ip VARCHAR(36), city VARCHAR(36), region VARCHAR(36), country VARCHAR(36), continent VARCHAR(36));";

	public static function runAsync(Query $query, ?callable $callable = null): void
	{
		$query
			->setHost(MySQL['host'])
			->setUser(MySQL['user'])
			->setPassword(MySQL['password'])
			->setDatabase(MySQL['database'])
		;

		self::$callbacks[spl_object_hash($query)] = $callable;
		Server::getInstance()->getAsyncPool()->submitTask($query);
	}

	public static function submitAsync(Query $query): void
	{
		$callable = self::$callbacks[spl_object_hash($query)] ?? null;

		if (is_callable($callable)) {
			$callable($query['rows']);
		}
	}

	public static function createTables(): void
	{
		foreach ([self::CREATE_TABLE_SERVERS, self::CREATE_PLAYER_CONFIG, self::CREATE_PLAYER_LOCATION] as $query) {
			self::runAsync(new InsertQuery($query));
		}
	}
}