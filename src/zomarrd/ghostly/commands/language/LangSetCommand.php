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
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\player\permission\PermissionKey;

final class LangSetCommand extends BaseSubCommand
{

	/**
	 * @todo Update the language of the player in the database?
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if ((count($args) === 1) && isset($args["language|player"])) {
			$target = $args["language|player"];
			if (!$sender instanceof GhostlyPlayer) {
				$sender->sendMessage(PREFIX . '§c' . 'This command must be executed in-game.');
			} else {
				foreach (LangHandler::getInstance()->getLanguages() as $language) {
					if ($language->getLocale() !== $target) {
						$sender->sendTranslated(LangKey::COMMAND_LANG_LIST);
						return;
					}

					$sender->setLanguage($target);
					$sender->sendTranslated(LangKey::LANG_APPLIED_CORRECTLY, ["{NEW-LANG}" => $sender->getLocale()]);
				}
			}
		}

		if (!isset($args["language"])) {
			return;
		}

		$target = $args["language|player"];
		$newLang = $args["language"];
		$isPlayer = Server::getInstance()->getPlayerByPrefix($target);

		if ($target === $sender->getName()) {
			$sender->sendMessage(PREFIX . 'Use: </lang set [language]>');
			return;
		}

		if (!$sender->hasPermission(PermissionKey::GHOSTLY_COMMAND_LANG_SET_OTHER)) {
			if ($sender instanceof GhostlyPlayer) {
				$sender->sendTranslated(LangKey::NOT_PERMISSION);
			}
			return;
		}

		if (!$isPlayer instanceof GhostlyPlayer || !$isPlayer->isOnline()) {
			if ($sender instanceof GhostlyPlayer) {
				$sender->sendTranslated(LangKey::PLAYER_NOT_ONLINE, ["{PLAYER-NAME}" => $target]);
			} else {
				$sender->sendMessage(PREFIX . "Player $target is not connected.");
			}
			return;
		}

		foreach (LangHandler::getInstance()->getLanguages() as $language) {
			if ($language->getLocale() !== $newLang) {
				if ($sender instanceof GhostlyPlayer) {
					$sender->sendTranslated(LangKey::COMMAND_LANG_LIST);
				} else {
					$sender->sendMessage(PREFIX . 'Use </lang list> to see the list of available languages');
				}
				return;
			}

			$isPlayer->setLanguage($newLang);
			$isPlayer->sendTranslated(LangKey::LANG_APPLIED_CORRECTLY, ["{NEW-LANG}" => $isPlayer->getLang()->getLocale()]);
		}
	}

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void
	{
		$this->registerArgument(0, new RawStringArgument('language|player', false));
		$this->registerArgument(1, new RawStringArgument('language', true));
	}
}