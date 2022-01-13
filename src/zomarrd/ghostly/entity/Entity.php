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
	public const EXTRA = "{JOIN}";
	public const DISCORD = "discord";
	public const STORE = "store";
	public const OMAR = "zomarrd";

	public const COMBO = "combo";
	public const PRACTICE = "practice";
	public const UHC = "uhc";
	public const UHC_RUN = "uhc_run";
	public const HCF = "hcf";
	public const KITMAP = "kitmap";


	use EnumTrait;

	protected static function setup(): void
	{
		self::_registryRegister("entity", new EntityManager());
	}
}