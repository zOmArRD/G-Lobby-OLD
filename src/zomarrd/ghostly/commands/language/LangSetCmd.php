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
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\permission\PermissionKeys;

final class LangSetCmd extends BaseSubCommand
{

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void
	{
		$this->registerArgument(0, new RawStringArgument('language|player', false));
		$this->registerArgument(1, new RawStringArgument('language', true));
	}

	/**
	 * @todo Update the language of the player in the database?
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if ((count($args) === 1) && isset($args["language|player"])) {
			$target = $args["language|player"];
			if ($sender instanceof GhostlyPlayer) {
				foreach (LangHandler::getInstance()->getLanguages() as $language) {
					if ($language->getLocale() === $target) {
						$sender->setLanguage($target);
						$sender->sendTranslated('lang.message.set', ["{NEW-LANG}" => $sender->getLocale()]);
					} else {
						$sender->sendMessage(PREFIX . 'Use: </lang list> to see the list of available languages');
						return;
					}
				}
			} else {
				$sender->sendMessage(PREFIX . '§c' . 'This command must be executed in-game.');
			}
		} elseif (isset($args["language"])) {
			if ($sender->hasPermission(PermissionKeys::GHOSTLY_COMMAND_LANG_SET_OTHER)) {
				if ($sender instanceof GhostlyPlayer) {
					$sender->sendTranslated('global.permission.message');
				}
				return;
			}
			$target = $args["language|player"];
			$newLang = $args["language"];
			$isPlayer = Server::getInstance()->getPlayerByPrefix($target);
			if ($isPlayer instanceof GhostlyPlayer) {
				if ($isPlayer->getName() === $sender->getName()) {
					$sender->sendMessage(PREFIX . 'Use: </lang set [language]>');
					return;
				}
				foreach (LangHandler::getInstance()->getLanguages() as $language) {
					if ($language->getLocale() === $newLang) {
						$isPlayer->setLanguage($newLang);
						$isPlayer->sendTranslated('lang.message.set', ["{NEW-LANG}" => $isPlayer->getLocale()]);
					} else {
						$isPlayer->sendMessage(PREFIX . 'Use </lang list> to see the list of available languages');
						return;
					}
				}
			} else {
				$sender->sendMessage(PREFIX . "Player $target is not connected.");
			}
		}
	}
}