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

namespace zomarrd\ghostly\events;

use pocketmine\event\Listener;
use pocketmine\world\sound\ExplodeSound;
use zomarrd\ghostly\entity\Entity;
use zomarrd\ghostly\entity\events\HumanInteractEvent;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\server\ServerManager;

final class HumanListener implements Listener
{
	public function handler(HumanInteractEvent $event): void
	{
		$player = $event->getPlayer();
		$entity = $event->getEntity();

		$i = $entity->getNpcId();

		if ($i === Entity::DISCORD) {
			$player->sendTranslated(LangKey::DISCORD_INVITATION_MESSAGE);
		}

		if ($i === Entity::STORE) {
			$player->sendTranslated(LangKey::STORE_LINK_MESSAGE);
		}

		if ($i === Entity::COMBO || $i === Entity::PRACTICE || $i === Entity::UHC || $i === Entity::UHC_RUN || $i === Entity::KITMAP || $i === Entity::HCF) {
			$server = ServerManager::getInstance()->getServerByName($entity->getServerName());

			if (is_null($server)) {
				$player->knockBack(($player->getLocation()->x - ($entity->getLocation()->x + 0.5)), ($player->getLocation()->z - ($entity->getLocation()->z + 0.5)), (20 / 0xa));
				$player->broadcastSound(new ExplodeSound(), [$player]);
				$player->sendTranslated(LangKey::SERVER_CONNECT_ERROR_3);
				return;
			}

			$player->transferTo($server);
		}
	}
}