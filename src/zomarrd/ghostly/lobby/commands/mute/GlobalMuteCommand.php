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

namespace zomarrd\ghostly\lobby\commands\mute;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\player\permission\PermissionKey;

final class GlobalMuteCommand extends BaseCommand
{
    public function __construct(Plugin $plugin, string $name)
    {
        parent::__construct($plugin, $name, 'Activates/Deactivates the global mute', ['glm']);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $value = $args['value'];
        if ($value) {
            if (!Ghostly::isGlobalMute()) {
                $sender->sendMessage(PREFIX . '§aYou have activated the global mute');
            } else {
                $sender->sendMessage(PREFIX . '§cYou cannot activate the global mute, apparently it is already activated!');
                return;
            }
        } else if (Ghostly::isGlobalMute()) {
            $sender->sendMessage('§aYou have deactivated the global mute');
        } else {
            $sender->sendMessage('§cYou cannot deactivate the global mute, apparently it is already deactivated!');
            return;
        }

        Ghostly::setGlobalMute($value);
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission(PermissionKey::GHOSTLY_COMMAND_GLOBAL_MUTE);
        $this->registerArgument(0, new BooleanArgument('value'));
    }
}