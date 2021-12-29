<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\world;

use pocketmine\world\Position;
use pocketmine\world\World;

final class Lobby
{
	public static ?Lobby $instance = null;

	public function __construct(
		private World     $world,
		private int       $spawnX,
		private int       $spawnY,
		private int       $spawnZ,
		private float|int $spawnYaw,
		private float|int $spawnPitch,
		private int       $minVoid
	){self::$instance = $this;}

	/**
	 * @return Lobby|null
	 */
	public static function getInstance(): ?Lobby
	{
		return self::$instance;
	}

	public function getDisplayName(): string
	{
		return $this->getWorld()->getDisplayName();
	}

	public function getWorld(): World
	{
		return $this->world;
	}

	public function getFolderName(): string
	{
		return $this->getWorld()->getFolderName();
	}

	public function getSpawnYaw(): float|int
	{
		return $this->spawnYaw;
	}

	public function getSpawnPitch(): float|int
	{
		return $this->spawnPitch;
	}

	public function getMinVoid(): int
	{
		return $this->minVoid;
	}

	public function getSpawnPosition(): Position
	{
		return new Position($this->getSpawnX(), $this->getSpawnY(), $this->getSpawnZ(), $this->getWorld());
	}

	public function getSpawnX(): int
	{
		return $this->spawnX;
	}

	public function getSpawnY(): int
	{
		return $this->spawnY;
	}

	public function getSpawnZ(): int
	{
		return $this->spawnZ;
	}
}