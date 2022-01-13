<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 15/12/2021
 *
 * Copyright © 2021 Ghostly Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\entity;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\entity\Entity;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\utils\Utils;

final class EntityCreateCommand extends BaseSubCommand
{

	/**
	 * @param CommandSender $sender
	 * @param string        $aliasUsed
	 * @param array         $args
	 *
	 * @return void
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (!$sender instanceof GhostlyPlayer) {
			$sender->sendMessage(Utils::ONLY_PLAYER);
			return;
		}
		$type = strtolower($args["type"]);

		switch ($type) {
			case "discord":
				Entity::ENTITY()->entity_discord($sender);
				break;
			case "store":
			Entity::ENTITY()->entity_store($sender);
				break;
			case "zomarrd":
				Entity::ENTITY()->spawn_zOmArRD($sender);
			break;
			case "lucy":
				Entity::ENTITY()->spawn_Lucy($sender);
				break;
			case "combo":
				Entity::ENTITY()->npc_combo($sender);
				break;
			case "practice":
				Entity::ENTITY()->npc_practice($sender);
				break;
			case "uhc":
			Entity::ENTITY()->npc_uhc($sender);
				break;
			case "uhcrun":
				Entity::ENTITY()->npc_uhc_run($sender);
				break;
			case "hcf":
			Entity::ENTITY()->npc_hcf($sender);
				break;
			case "kitmap":
			Entity::ENTITY()->npc_kitmap($sender);
				break;
			default:
				$sender->sendMessage(PREFIX . "§cThis entity does not exist!");
				return;
		}
		$sender->sendMessage("The entity {$args["type"]} has been spawned!");
	}

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 *
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void
	{
		$this->registerArgument(0, new RawStringArgument("type"));
	}
}