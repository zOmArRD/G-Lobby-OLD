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

namespace zomarrd\ghostly\lobby\commands\language;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\Language;

final class LangCommand extends BaseCommand
{

    public function __construct(Plugin $plugin, string $name)
    {
        parent::__construct($plugin, $name, "Change language", ['idioma', 'language']);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof GhostlyPlayer && count($args) === 0) {
            Language::openLangForm($sender);
            return;
        }

        $this->sendUsage();
    }

    protected function prepare(): void
    {
        $this->registerSubCommand(new LangListCommand('list', "List of available languages"));
        $this->registerSubCommand(new LangSetCommand('set', "Set your language, or someone else's"));
    }
}