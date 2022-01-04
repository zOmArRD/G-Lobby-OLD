<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 3/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\mute;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\permission\PermissionKey;

final class GlobalMuteCommand extends BaseCommand
{
	public function __construct(Plugin $plugin, string $name)
	{
		$this->setPermission(PermissionKey::GHOSTLY_COMMAND_GLOBAL_MUTE);
		parent::__construct($plugin, $name, 'Activates/Deactivates the global mute', ['glm']);
	}

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void
	{
		$this->registerArgument(0, new BooleanArgument('value'));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		$value = $args["value"];
		if ($value) {
			if (Ghostly::isGlobalMute()) {
				$sender->sendMessage(PREFIX . "§cYou cannot activate the global mute, apparently it is already activated!");
				return;
			}
			$sender->sendMessage(PREFIX . "§aYou have activated the global mute");
		} else {
			if (!Ghostly::isGlobalMute()) {
				$sender->sendMessage("§cYou cannot deactivate the global mute, apparently it is already deactivated!");
				return;
			}
			$sender->sendMessage("§aYou have deactivated the global mute");
		}
		Ghostly::setGlobalMute($value);
	}
}