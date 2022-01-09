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

namespace zomarrd\ghostly\entity;

use pocketmine\utils\EnumTrait;

/**
 * @method static EntityManager ENTITY()
 */
final class Entity
{
	use EnumTrait;

	protected static function setup(): void
	{
		self::_registryRegister("entity", new EntityManager());
	}
}