<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use zomarrd\ghostly\player\GhostlyPlayer;

final class PlayerEvents implements Listener
{
	public function onCreation(PlayerCreationEvent $event): void
	{
		$event->setPlayerClass(GhostlyPlayer::class);
	}

	public function onJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();
		if (!$player instanceof GhostlyPlayer) {
            return;
        }
		$player->onJoin();
	}

	public function onExhaust(PlayerExhaustEvent $event): void
	{
		$event->cancel();
	}
}