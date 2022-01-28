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

namespace zomarrd\ghostly\entity\type;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

final class FloatingTextType extends Entity
{
	public $canCollide = false;
	protected $gravity = 0.0;
	protected $immobile = true;
	protected $scale = 0.001;
	protected string $textId;


	public function __construct(Location $location, CompoundTag $nbt)
	{
		parent::__construct($location, $nbt);

		$this->setNameTagAlwaysVisible();
		$this->setNameTagVisible();
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::BAT;
	}

	public function canBeMovedByCurrents(): bool
	{
		return false;
	}

	public function setTextId(string $textId): void
	{
		$this->textId = $textId;
	}

	public function getTextId(): string
	{
		return $this->textId;
	}

	public function saveNBT(): CompoundTag
	{
		$nbt = CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($this->location->x),
				new DoubleTag($this->location->y),
				new DoubleTag($this->location->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($this->motion->x),
				new DoubleTag($this->motion->y),
				new DoubleTag($this->motion->z)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($this->location->yaw),
				new FloatTag($this->location->pitch)
			]));

		EntityFactory::getInstance()->injectSaveId(get_class($this), $nbt);
		if ($this->getNameTag() !== "") {
			$nbt->setString("CustomName", $this->getNameTag());
			$nbt->setByte("CustomNameVisible", $this->isNameTagVisible() ? 1 : 0);
		}

		$nbt->setFloat("FallDistance", $this->fallDistance);
		$nbt->setShort("Fire", $this->fireTicks);
		$nbt->setByte("OnGround", $this->onGround ? 1 : 0);
		$nbt->setString("TextId", $this->textId);

		return $nbt;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(0.001, 0.001, 0.001);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		$this->fireTicks = $nbt->getShort("Fire", 0);
		$this->onGround = $nbt->getByte("OnGround", 0) !== 0;
		$this->fallDistance = $nbt->getFloat("FallDistance", 0.0);
		$this->textId = $nbt->getString("TextId");
		if(($customNameTag = $nbt->getTag("CustomName")) instanceof StringTag){
			$this->setNameTag($customNameTag->getValue());
			if(($customNameVisibleTag = $nbt->getTag("CustomNameVisible")) instanceof StringTag){
				$this->setNameTagVisible($customNameVisibleTag->getValue() !== "");
			}else{
				$this->setNameTagVisible($nbt->getByte("CustomNameVisible", 1) !== 0);
			}
		}
	}
}