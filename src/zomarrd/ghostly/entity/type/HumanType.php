<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 9/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\entity\type;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;

final class HumanType extends Human
{
	public $canCollide = false;
	protected $immobile = true;
	protected $gravity = 0.0;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null)
	{
		parent::__construct($location, $skin, $nbt);
		$this->setNameTagAlwaysVisible();
		$this->setNameTagVisible();
	}

	public function canBeMovedByCurrents(): bool
	{
		return false;
	}
}