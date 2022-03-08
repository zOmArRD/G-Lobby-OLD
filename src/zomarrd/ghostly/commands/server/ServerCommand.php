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

namespace zomarrd\ghostly\commands\server;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\menu\Menu;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\permission\PermissionKey;

final class ServerCommand extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        $this->setPermission(PermissionKey::GHOSTLY_COMMAND_SERVER);
        parent::__construct($plugin, 'server', 'Server Administration');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof GhostlyPlayer) {
            Menu::SERVER_MANAGER_FORM()->build($sender);
            return;
        }

        $this->sendUsage();
    }

    protected function prepare(): void
    {
        $this->registerSubCommand(new ServerReloadCommand('reload', 'Reload servers from database'));
    }
}