<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 27/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\network\proxy;

use Exception;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use zomarrd\ghostly\lobby\database\Database;
use zomarrd\ghostly\lobby\database\mysql\queries\InsertQuery;
use zomarrd\ghostly\lobby\database\mysql\queries\SelectQuery;
use zomarrd\ghostly\lobby\database\mysql\queries\UpdateRowQuery;
use zomarrd\ghostly\lobby\player\permission\PermissionKey;

final class AntiProxy extends AsyncTask
{

    public function __construct(private string $player, private string $ip) {}

    public function onRun(): void
    {
        $url = "https://vpnapi.io/api/" . $this->getIp() . "?key=368a3b3454284459a204f5808e02f581";

        try {
            $this->setResult(json_decode(file_get_contents($url), false, 512, JSON_THROW_ON_ERROR));
        } catch (Exception) {
            $this->getPlayer()->kick(PREFIX . "Your login could not be confirmed, contact our support!");
        }
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPlayer(): Player
    {
        return Server::getInstance()->getPlayerExact($this->player);
    }

    public function onCompletion(): void
    {
        $result = $this->getResult();
        $playerName = $this->player;

        if ($this->player === "") {
            return;
        }

        if (!isset($result->{'security'}, $result->{'location'})) {
            $this->getPlayer()->disconnect(PREFIX . "Your login could not be confirmed, contact our support!");
            return;
        }

        $security = $result->{'security'};
        $location = $result->{'location'};
        $xuid = $this->getPlayer()->getXuid();

        if (!$this->getPlayer()->hasPermission(PermissionKey::GHOSTLY_PROXY_BYPASS)) {
            if ($security->vpn || $security->tor || $security->proxy) {
                $this->getPlayer()->disconnect(PREFIX . "We do not accept VPN on our network, if you want to enter with VPN buy rank!");
            }
        }

        $ip = $result->{'ip'};

        // Make a ban system that detects alts (ip with the same accounts, etc.)
        Database::getMysql()->runAsync(new SelectQuery("SELECT * FROM player_location WHERE xuid = '$xuid';"), static function($result) use ($playerName, $xuid, $ip, $location): void {
            if (count($result) === 0) {
                Database::getMysql()->runAsync(new InsertQuery(sprintf("INSERT INTO player_location(player, xuid, ip, city, region, country, continent) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s');", $playerName, $xuid, $ip, $location->{'city'}, $location->{'region'}, $location->{'country'}, $location->{'continent'})));
            } else {
                /** Add a method to find alts */
                Database::getMysql()->runAsync(new UpdateRowQuery(serialize([
                    "ip" => $ip,
                    "city" => $location->{'city'},
                    "region" => $location->{'region'},
                    "country" => $location->{'country'},
                    "continent" => $location->{'continent'}
                ]), "xuid", $xuid, "player_location"));
            }
        });
    }
}