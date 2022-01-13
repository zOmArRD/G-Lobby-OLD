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
use zomarrd\ghostly\entity\Entity;
use zomarrd\ghostly\entity\events\HumanInteractEvent;
use zomarrd\ghostly\player\language\LangKey;

final class HumanListener implements Listener
{
	public function handler(HumanInteractEvent $event): void
	{
		$player = $event->getPlayer();
		$entity = $event->getEntity();

		switch ($entity->getNpcId()) {
			case Entity::DISCORD:
				$player->sendTranslated(LangKey::DISCORD_INVITATION_MESSAGE);
				break;
			case Entity::STORE:
				$player->sendTranslated(LangKey::STORE_LINK_MESSAGE);
				break;
			case Entity::COMBO:
				$player->sendMessage("work");
				break;
		}
	}
}