<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 16/12/2021
 *
 * Copyright © 2021 Ghostly Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\commands\entity;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use zomarrd\ghostly\lobby\entity\Entity;

final class EntityKillCommand extends BaseSubCommand
{
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (count($args) < 0) {
            $this->sendError(BaseCommand::ERR_INSUFFICIENT_ARGUMENTS);
            return;
        }

        if (isset($args['isAll']) && ($args['isAll'] === true)) {
            Entity::ENTITY()->purge_all();
            $sender->sendMessage(PREFIX . '§aYou have purged all entities!');
            return;
        }

        $type = $args['EntityId'] ?? null;

        switch ($type) {
            case Entity::DISCORD:
            case Entity::STORE:
                Entity::ENTITY()->remove_entity($type);
                $sender->sendMessage(sprintf('%s§aYou have purged the entity %s!', PREFIX, $type));
                return;
            default:
                Entity::ENTITY()->remove_entity($type);
                $sender->sendMessage(PREFIX . '§cWe will try to delete this entity!');
                break;
        }
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new BooleanArgument('isAll', false));
        $this->registerArgument(0, new RawStringArgument('EntityId', true));
    }
}