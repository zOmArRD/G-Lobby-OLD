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

namespace zomarrd\ghostly\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\player\permission\Permission;

final class ServerCommand extends BaseCommand
{
	public function __construct(Plugin $plugin, string $name)
	{
		$this->setPermission(Permission::COMMAND_SERVER);
		parent::__construct($plugin, $name, 'Server Command');
	}

	protected function prepare(): void
	{
		// TODO: Implement prepare() method.
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		// TODO: Implement onRun() method.
	}
}
