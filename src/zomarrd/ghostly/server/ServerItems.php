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

namespace zomarrd\ghostly\server;

use JetBrains\PhpStorm\Pure;
use pocketmine\item\Item;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\utils\Utils;

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
                $final = Utils::array_replace_values($array, "§r§7Players: §f{SERVER_GET-PLAYERS}§7/§f{SERVER_GET-MAX-PLAYERS}\n", "§r§7Players: §f{$final->getPlayers()}§7/§f{$final->getMaxPlayers()}\n");
                $item->setLore($final);
            } else {
                $item->setLore(Ghostly::$server_items->get($server)['offline']);
            }
        }

        return $item;
    }

    #[Pure] public static function getServer(string $server): ?Server
    {
        return ServerManager::getInstance()->getServerByName($server);
    }

    public static function setDefaultLore(Item $item): void
    {
        $item->setLore(Ghostly::$server_items->get('no-exist'));
    }
}