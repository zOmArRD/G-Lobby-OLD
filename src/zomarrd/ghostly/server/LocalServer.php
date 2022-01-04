<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 4/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\server;

final class LocalServer
{
	private static LocalServer $instance;

	public function __construct(
		private array $servers
	){self::$instance = $this;}

	public static function getInstance(): LocalServer
	{
		return self::$instance;
	}

	public function getServers(): array
	{
		return $this->servers;
	}

	public function getServerByName(string $name): array|null
	{
		foreach ($this->getServers() as $server) {
			if ($server["server_name"] === $name) {
				return $server;
			}
		}
		return null;
	}

	public function getCurrentServer(): array|null
	{
		foreach ($this->getServers() as $server) {
			if ($server["is_current"] === true) {
				return $server;
			}
		}
		return null;
	}
}

