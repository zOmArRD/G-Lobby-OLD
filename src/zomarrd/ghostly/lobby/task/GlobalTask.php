<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 11/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\task;

use GhostlyMC\GCoinsAPI\GCoins;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use zomarrd\ghostly\lobby\entity\Entity;
use zomarrd\ghostly\lobby\entity\EntityManager;
use zomarrd\ghostly\lobby\menu\Menu;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\server\ServerList;

final class  GlobalTask extends Task
{
    /*protected $int = 0;
    private array $armors;*/

    /*public function __construct()
    {
        $this->armors = [
            VanillaItems::LEATHER_CAP(),
            VanillaItems::LEATHER_TUNIC(),
            VanillaItems::LEATHER_PANTS(),
            VanillaItems::LEATHER_BOOTS()
        ];
    }*/

    public function onRun(int $currentTick): void
    {
        if ($currentTick % 20 === 0) {
            if (EntityManager::$count > 2) {
                EntityManager::$count = 0;
            }

            Entity::ENTITY()->update_server_status(ServerList::HCF, EntityManager::$count);
            Entity::ENTITY()->update_server_status(ServerList::COMBO, EntityManager::$count);
            Entity::ENTITY()->update_server_status(ServerList::PRACTICE, EntityManager::$count);
            Entity::ENTITY()->update_server_status(ServerList::KITMAP, EntityManager::$count);
            Entity::ENTITY()->update_server_status(ServerList::UHC, EntityManager::$count);
            Entity::ENTITY()->update_server_status(ServerList::UHCRUN, EntityManager::$count);

            EntityManager::$count++;
        }

        if ($currentTick % 50 === 0) {
            Menu::SERVER_SELECTOR()->prepare();
            Menu::LOBBY_SELECTOR()->prepare();
        }


        /*if ($currentTick % 100 === 0) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if (!$player instanceof GhostlyPlayer) {
                    continue;
                }

                GCoins::getInstance()->updateBalance($player);
            }
        }*/

        /*$armors = array_map(function (Armor $armor): Armor {

            if ($this->int >= 32) { //flex
                $this->int = 0;
            }

            $tmp = Ghostly::$colors[$this->int];
            $armor->setCustomColor(new Color((int)$tmp["r"], (int)$tmp["g"], (int)$tmp["b"]));

            return $armor;
        }, $this->armors);
        $this->int++;

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->getArmorInventory()->setContents($armors);
        }*/
    }
}