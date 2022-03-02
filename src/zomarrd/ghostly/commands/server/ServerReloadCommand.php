<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 1/3/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\server;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\server\ServerManager;

final class ServerReloadCommand extends BaseSubCommand
{
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        ServerManager::getInstance()->reloadServers();
        $sender->sendMessage(PREFIX . "Servers have been reloaded from the database!");
    }

    protected function prepare(): void { }
}