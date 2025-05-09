<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 30/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\server;

use pocketmine\item\Item;
use zomarrd\ghostly\lobby\Ghostly;

final class ServerItems
{
    public static function get(string $server, Item $item): Item
    {
        $final = self::getServer($server);
        $item->setCustomName('§r§l§c' . $server);
        self::setDefaultLore($item);

        if (isset($final)) {
            if ($final->isOnline()) {
                $array = Ghostly::$server_items->get($server)['online'];
                $final = arrayReplaceValues($array, "§r§7Players: §f{SERVER_GET-PLAYERS}§7/§f{SERVER_GET-MAX-PLAYERS}\n", sprintf("§r§7Players: §f%s§7/§f%s\n", $final->getOnlinePlayers(), $final->getMaxPlayers()));
                $item->setLore($final);
            } else {
                $item->setLore(Ghostly::$server_items->get($server)['offline']);
            }
        }

        return $item;
    }

    public static function getServer(string $server): ?Server
    {
        return ServerManager::getInstance()->getServerByName($server);
    }

    public static function setDefaultLore(Item $item): void
    {
        $item->setLore(Ghostly::$server_items->get('no-exist'));
    }
}