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

namespace zomarrd\ghostly\mysql\queries;

use mysqli;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\Query;

final class RegisterServerQuery extends Query
{
	public function __construct(
		private string $serverName
	){}

	public function query(mysqli $mysqli): void
	{
		$result = $mysqli->query("SELECT * FROM ghostly_servers WHERE server_name = '$this->serverName';");
		if ($result !== false) {
			$assco = $result->fetch_assoc();
			if (is_array($assco)) {
				$mysqli->query("UPDATE ghostly_servers SET online = 1 WHERE server_name = '$this->serverName';");
			} else {
				$proxy = Ghostly::PROXY_TRANSFER;
				$address = Ghostly::ADDRESS;
				$category = Ghostly::CATEGORY;

				$mysqli->query("INSERT INTO ghostly_servers(server_name, players, max_players, online, whitelist, proxy_transfer, category, address) VALUES ('$this->serverName', 0, 0, true, true, $proxy, '$category', '$address');");
			}
		} else {
			MySQL::runAsync(new RegisterServerQuery($this->serverName));
		}
	}
}