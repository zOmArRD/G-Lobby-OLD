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

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\world\World;
use zomarrd\ghostly\entity\type\FloatingTextType;
use zomarrd\ghostly\entity\type\HumanType;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\world\Lobby;

final class EntityManager
{
	public function register(): void
	{
		EntityFactory::getInstance()->register(HumanType::class, function (World $world, CompoundTag $tag): HumanType {
			return new HumanType(EntityDataHelper::parseLocation($tag, $world), HumanType::parseSkinNBT($tag), $tag);
		}, ["HumanType"]);

		EntityFactory::getInstance()->register(FloatingTextType::class, function (World $world, CompoundTag $tag): FloatingTextType {
			return new FloatingTextType(EntityDataHelper::parseLocation($tag, $world), $tag);
		}, ["FloatingTextType"]);

	}

	public function entity_discord(GhostlyPlayer $player): void
	{
		$location = $player->getLocation();
		$skin = $player->getSkin();
		$this->remove_npc("discord");

		$entity = new HumanType($location, new Skin("discord", $skin->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData()));
		$entity->setNameTag("§r§eClick to join our Discord server!");
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		foreach ([
					 "§l§9Discord Server" => 3.80,
					 "Join our Discord community to keep" => 3.40,
					 "up-to-date about recent updates," => 3.00,
					 "giveaways, suggest us ideas or" => 2.60,
					 "appeal a punishment." => 2.20
				 ] as $text => $mY) {
			$this->floating_text($text, "discord", new Location($x, $y + $mY, $z, $eLocation->getWorld(), 0.0, 0.0));
		}
	}

	public function remove_npc(string $npcId): void
	{
		$lobby = Lobby::getInstance();
		$world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();

		if (!isset($world)) {
			return;
		}

		foreach ($world->getEntities() as $entity) {
			if (($entity instanceof HumanType) && $entity->getSkin()->getSkinId() === $npcId) {
				$entity->kill();
			}

			if (($entity instanceof FloatingTextType) && $entity->getTextId() === $npcId) {
				$entity->kill();
			}
		}
	}

	public function floating_text(string $text, string $id, Location $location, bool $spawnToAll = true, GhostlyPlayer $player = null): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("TextId", $id);

		$entity = new FloatingTextType($location, $nbt);
		$entity->setNameTag("§r§7" . $text);

		if ($spawnToAll) {
			$entity->spawnToAll();
		} else {
			if (is_null($player)) {
				throw new \RuntimeException("Cannot spawn entity to NULL player");
			}
			$entity->spawnTo($player);
		}
	}

	public function entity_store(GhostlyPlayer $player): void
	{
		$location = $player->getLocation();
		$skin = $player->getSkin();
		$this->remove_npc("store");

		$entity = new HumanType($location, new Skin("store", $skin->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData()));
		$entity->setNameTag("§r§eClick to view store!");
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		foreach ([
					 "§l§aStore" => 3.10,
					 "You can purchase G-Coins, ranks" => 2.70,
					 "and tags on our store!." => 2.30
				 ] as $text => $mY) {
			$this->floating_text($text, "store", new Location($x, $y + $mY, $z, $eLocation->getWorld(), 0.0, 0.0));
		}
	}

	public function spawn_zOmArRD(GhostlyPlayer $player): void
	{
		$location = $player->getLocation();
		$skin = $player->getSkin();
		$this->remove_npc("zomarrd");

		$entity = new HumanType($location, new Skin("zomarrd", $skin->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData()));
		$entity->setNameTag("§r§4zOmArRD");
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§7[§cDev§7]", "zomarrd", new Location($x, $y + 2.15, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function purge_all(): void
	{
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $entity) {
				if (!$entity instanceof GhostlyPlayer) {
					$entity->kill();
				}
			}
		}
	}
}