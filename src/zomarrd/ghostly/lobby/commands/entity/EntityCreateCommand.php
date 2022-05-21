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
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\lobby\entity\Entity;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\server\ServerList;

/**
 * Here we handle the creation of the entities on the server.
 */
final class EntityCreateCommand extends BaseSubCommand
{

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof GhostlyPlayer) {
            $sender->sendMessage(ONLY_PLAYERS);
            return;
        }

        $type = $args['type'];

        switch ($type) {
            case $sender->getName():
                if (isset($args['format'])) {
                    $explode = explode(':', $args['format']);
                    Entity::ENTITY()->spawnHuman($sender, $type, $explode);
                }
                break;
            case Entity::DISCORD:
                Entity::ENTITY()->entity_discord($sender);
                break;
            case Entity::STORE:
                Entity::ENTITY()->entity_store($sender);
                break;
            case ServerList::COMBO:
            case ServerList::PRACTICE:
            case ServerList::UHC:
            case ServerList::UHCRUN:
            case ServerList::HCF:
            case ServerList::KITMAP:
                Entity::ENTITY()->createEntityServer($sender, $type);
                break;
            default:
                $sender->sendMessage(PREFIX . '§cThis entity does not exist!');
                return;
        }

        $sender->sendMessage(sprintf('%s§aThe entity %s has been spawned!', PREFIX, $args['type']));
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument('type'));
        $this->registerArgument(1, new TextArgument('format', true));
    }
}