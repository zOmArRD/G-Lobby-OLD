<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 1/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\commands\language;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\language\LangKey;

final class LangListCommand extends BaseSubCommand
{
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $line = str_repeat('-----', 3);

        if (!$sender instanceof GhostlyPlayer) {
            $sender->sendMessage(PREFIX . "Available languages");
        } else {
            $sender->sendMessage(PREFIX . $sender->getTranslation(LangKey::AVAILABLE_LANGUAGE));
        }

        $sender->sendMessage($line . $line);

        foreach (LangHandler::getInstance()->getLanguages() as $language) {
            $sender->sendMessage("§a-§7 {$language->getLocale()} => {$language->getName()}");
        }

        $sender->sendMessage($line . $line);
    }

    protected function prepare(): void {}
}