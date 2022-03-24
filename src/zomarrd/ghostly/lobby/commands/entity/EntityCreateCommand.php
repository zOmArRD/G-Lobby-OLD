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

namespace zomarrd\ghostly\lobby\commands\entity;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\lobby\entity\Entity;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\server\Server;
use zomarrd\ghostly\lobby\utils\Utils;

final class EntityCreateCommand extends BaseSubCommand
{

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof GhostlyPlayer) {
            $sender->sendMessage(Utils::ONLY_PLAYER);
            return;
        }

        $type = $args["type"];

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
            case Server::COMBO:
            case Server::PRACTICE:
            case Server::UHC:
            case Server::UHC_RUN:
            case Server::HCF:
            case Server::KITMAP:
                Entity::ENTITY()->createEntityServer($sender, $type);
                break;
            default:
                $sender->sendMessage(PREFIX . "§cThis entity does not exist!");
                return;
        }

        $sender->sendMessage(PREFIX . "The entity {$args["type"]} has been spawned!");
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("type"));
    }
}