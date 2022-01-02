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
	private static array $callbacks = [];

	private const TABLE_SERVERS = 'CREATE TABLE IF NOT EXISTS network_servers(server_name VARCHAR(16), players INT, max_players INT, online BOOLEAN, whitelist BOOLEAN)';

	public static function runAsync(Query $query, string $database = "network_servers", ?callable $callable = null): void
	{
		$query->setHost(MySQL['host'])
			->setUser(MySQL['user'])
			->setPassword(MySQL['password'])
			->setDatabase(MySQL['database'][$database]);
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
		foreach ([self::TABLE_SERVERS] as $query) {
			self::runAsync(new InsertQuery($query));
		}
	}
}