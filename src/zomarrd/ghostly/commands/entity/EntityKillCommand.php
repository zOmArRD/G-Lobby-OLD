<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 16/12/2021
 *
 * Copyright © 2021 Ghostly Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\entity;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use Exception;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\entity\Entity;

final class EntityKillCommand extends BaseSubCommand
{
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (count($args) < 0) {
			$this->sendError(BaseCommand::ERR_INSUFFICIENT_ARGUMENTS);
			return;
		}

		if (isset($args["isAll"]) && $args["isAll"] === true) {
			Entity::ENTITY()->purge_all();
			$sender->sendMessage(PREFIX . 'you have purged all entities!');
			return;
		}

		Entity::ENTITY()->remove_entity($args["EntityId"]);
		$sender->sendMessage(PREFIX . "you have purged the entity {$args["EntityId"]}!");
	}

	/**
	 * This is where all the arguments, permissions, sub-commands, etc. would be registered
	 */
	protected function prepare(): void
	{
		try {
			$this->registerArgument(0, new BooleanArgument("isAll", true));
			$this->registerArgument(0, new RawStringArgument("EntityId", true));
		} catch (Exception) {
		}
	}
}