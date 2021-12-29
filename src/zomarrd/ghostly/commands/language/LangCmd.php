<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\language;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\Language;

final class LangCmd extends BaseCommand
{

	public function __construct(Plugin $plugin, string $name)
	{
		parent::__construct($plugin, $name, "Change language", ['idioma', 'language']);
	}

	protected function prepare(): void
	{

	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if ($sender instanceof GhostlyPlayer) {
			Language::openLangForm($sender);
		} else {
			$this->sendUsage();
		}
	}
}