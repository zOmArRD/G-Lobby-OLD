<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 7/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\events;

use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use zomarrd\ghostly\lobby\entity\Entity;
use zomarrd\ghostly\lobby\entity\events\HumanInteractEvent;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\server\ServerManager;

/**
 * This function is in charge of handling the events of the entities on the server, mainly the npc to be transferred
 * from servers, and some other informative ones.
 */
final class HumanListener implements Listener
{
    public function handler(HumanInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $entity = $event->getEntity();
        $npcId = $entity->getNpcId();

        switch ($npcId) {
            case 'X6JGT':
                $player->sendMessage('§c(From zOmArRD: §8Hi, I am the creator of this network!§c)');
                /*$packet = new SetActorLinkPacket();
                $packet->link = new EntityLink($entity->getId(), $player->getId(), EntityLink::TYPE_RIDER, true, true);
                Server::getInstance()->broadcastPackets(Server::getInstance()->getOnlinePlayers(), [$packet]);*/
                break;
            case Entity::STORE:
                $player->sendTranslated(LangKey::STORE_LINK_MESSAGE);
                break;
            case Entity::DISCORD:
                $player->sendTranslated(LangKey::DISCORD_INVITATION_MESSAGE);
                break;
            default:
                if ($npcId === '') {
                    # DO NOTHING LOL
                    return;
                }

                $server = ServerManager::getInstance()->getServerByName($npcId);
                if (is_null($server)) {
                    $player->knockBack(($player->getLocation()->x - ($entity->getLocation()->x)), ($player->getLocation()->z - ($entity->getLocation()->z)), (20 / 0xa));
                    $player->sendSound(LevelSoundEvent::EXPLODE);
                    $player->sendTranslated(LangKey::SERVER_CONNECT_ERROR_3);
                    return;
                }

                Ghostly::getQueueManager()->add($player, $npcId);
                break;
        }
    }
}