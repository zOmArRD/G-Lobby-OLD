<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 6/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\entity\default;

use pocketmine\entity\Skin;
use pocketmine\Server;
use pocketmine\world\particle\FloatingTextParticle;
use zomarrd\ghostly\entity\IEntity;
use zomarrd\ghostly\entity\type\HumanType;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\world\Lobby;

final class Discord
{

	public const NPC_ID = "discord";

	public function spawn(GhostlyPlayer $player): void
	{
		$location = $player->getLocation();
		$skin = $player->getSkin();

		foreach ($location->getWorld()->getEntities() as $entity) {
			if ($entity instanceof HumanType && $entity->getSkin()->getSkinId() === self::NPC_ID) {
				$entity->kill();
			}
		}

		$human = new HumanType($location, new Skin(self::NPC_ID, $skin->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData()));
		$human->setNameTag("§r§eClick to join our Discord server");
		$human->setImmobile();
		$human->spawnToAll();
		new FloatingTextParticle('sd');
	}

	public function kill(): void
	{
		$lobby = Lobby::getInstance();
		if (isset($lobby)) {
			$world = $lobby->getWorld();
		} else {
			$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		}

		foreach ($world->getEntities() as $entity) {
			if ($entity instanceof HumanType && $entity->getSkin()->getSkinId() === self::NPC_ID) {
				$entity->kill();
			}
		}
	}
}