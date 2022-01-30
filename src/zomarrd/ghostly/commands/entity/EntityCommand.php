<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 7/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\entity;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\player\permission\PermissionKey;

final class EntityCommand extends BaseCommand
{
	public function __construct(Plugin $plugin, string $name)
	{
		parent::__construct($plugin, $name, "Entity manager", ['npc']);
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		$this->sendUsage();
	}

	protected function prepare(): void
	{
		$this->setPermission(PermissionKey::GHOSTLY_COMMAND_NPC);
		$this->registerSubCommand(
			new EntityCreateCommand(
				'create',
				'Create an entity'
			)
		);

		$this->registerSubCommand(
			new EntityKillCommand(
				'kill',
				'Delete entities, purge them all LOL',
				['purge']
			)
		);
	}
}