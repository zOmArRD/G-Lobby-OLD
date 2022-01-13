<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 11/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\task;

use pocketmine\scheduler\Task;
use zomarrd\ghostly\entity\Entity;

final class GlobalTask extends Task
{

	public function onRun(int $currentTick): void
	{
		if ($currentTick % 20 === 0) {

			Entity::ENTITY()->update_server_status();
		}
	}
}