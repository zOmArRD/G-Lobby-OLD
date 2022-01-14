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
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\world\World;
use zomarrd\ghostly\entity\type\FloatingTextType;
use zomarrd\ghostly\entity\type\HumanType;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\server\ServerManager;
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
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::DISCORD);

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::DISCORD);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNpcId(Entity::DISCORD);
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
			$this->floating_text($text, Entity::DISCORD, new Location($x, $y + $mY, $z, $eLocation->getWorld(), 0.0, 0.0));
		}
	}

	public function remove_entity(string $npcId): void
	{
		$lobby = Lobby::getInstance();
		$world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();

		if (!isset($world)) {
			return;
		}

		foreach ($world->getEntities() as $entity) {

			if (!$entity instanceof HumanType || $entity->getNpcId() !== $npcId) {
				continue;
			}

			$entity->kill();
			$this->kill_text($npcId);
			$this->kill_text($npcId . Entity::EXTRA);
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
			return;
		}

		if (!isset($player)) {
			throw new \RuntimeException("Player is NULL");
		}

		$entity->spawnTo($player);
	}

	public function entity_store(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::STORE);

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::STORE);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
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
			$this->floating_text($text, Entity::STORE, new Location($x, $y + $mY, $z, $eLocation->getWorld(), 0.0, 0.0));
		}
	}

	public function spawn_zOmArRD(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::OMAR);

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::OMAR);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§r§4zOmArRD");
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§7[§cDev§7]", Entity::OMAR, new Location($x, $y + 2.15, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function spawn_Lucy(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", "lucy");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity("lucy");

		$entity = new HumanType($location, new Skin("lucy", $skin->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData()), $nbt);
		$entity->setNameTag("§r§5LucyNept");
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§7[§5Lucy§7]", "lucy", new Location($x, $y + 2.15, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function npc_combo(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::COMBO);
		$nbt->setString("server_name", "Combo");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::COMBO);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§7Players: §f??§7/§f??");

		$enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);

		$item = VanillaItems::ENDER_PEARL();
		$chest_plate = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment($enchant);
		$leggings = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment($enchant);
		$boots = VanillaItems::DIAMOND_BOOTS()->addEnchantment($enchant);

		$armor = $entity->getArmorInventory();
		$armor->setChestplate($chest_plate);
		$armor->setLeggings($leggings);
		$armor->setBoots($boots);

		$entity->getInventory()->addItem($item);
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§l§cCombo", Entity::COMBO, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
		$this->floating_text("§eClick to join Combo.", Entity::COMBO . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function npc_practice(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::PRACTICE);
		$nbt->setString("server_name", "Practice");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::PRACTICE);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§7Players: §f??§7/§f??");

		$enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);

		$sword = VanillaItems::DIAMOND_SWORD()->addEnchantment($enchant);
		$chest_plate = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment($enchant);
		$leggings = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment($enchant);
		$boots = VanillaItems::DIAMOND_BOOTS()->addEnchantment($enchant);

		$armor = $entity->getArmorInventory();
		$armor->setChestplate($chest_plate);
		$armor->setLeggings($leggings);
		$armor->setBoots($boots);

		$entity->getInventory()->addItem($sword);
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§l§cPractice", Entity::PRACTICE, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
		$this->floating_text("§eClick to join Practice.", Entity::PRACTICE . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function npc_uhc(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::UHC);
		$nbt->setString("server_name", "UHC");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::UHC);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§7Players: §f??§7/§f??");

		$enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);

		$item = VanillaItems::GOLDEN_APPLE()->addEnchantment($enchant);
		$chest_plate = VanillaItems::GOLDEN_CHESTPLATE()->addEnchantment($enchant);
		$leggings = VanillaItems::GOLDEN_LEGGINGS()->addEnchantment($enchant);
		$boots = VanillaItems::GOLDEN_BOOTS()->addEnchantment($enchant);

		$armor = $entity->getArmorInventory();
		$armor->setChestplate($chest_plate);
		$armor->setLeggings($leggings);
		$armor->setBoots($boots);

		$entity->getInventory()->addItem($item);
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§l§cUHC", Entity::UHC, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
		$this->floating_text("§eClick to join UHC.", Entity::UHC . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function npc_uhc_run(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::UHC_RUN);
		$nbt->setString("server_name", "UHC_RUN");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::UHC_RUN);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§7Players: §f??§7/§f??");

		$enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);

		$item = VanillaItems::APPLE()->addEnchantment($enchant);
		$chest_plate = VanillaItems::GOLDEN_CHESTPLATE()->addEnchantment($enchant);
		$leggings = VanillaItems::GOLDEN_LEGGINGS()->addEnchantment($enchant);
		$boots = VanillaItems::GOLDEN_BOOTS()->addEnchantment($enchant);

		$armor = $entity->getArmorInventory();
		$armor->setChestplate($chest_plate);
		$armor->setLeggings($leggings);
		$armor->setBoots($boots);

		$entity->getInventory()->addItem($item);
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§l§cUHC Run", Entity::UHC_RUN, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
		$this->floating_text("§eClick to join UHC Run.", Entity::UHC_RUN . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function npc_hcf(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::HCF);
		$nbt->setString("server_name", "HCF");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::HCF);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§7Players: §f??§7/§f??");

		$enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);

		$item = VanillaItems::IRON_PICKAXE()->addEnchantment($enchant);
		$chest_plate = VanillaItems::GOLDEN_CHESTPLATE()->addEnchantment($enchant);
		$leggings = VanillaItems::GOLDEN_LEGGINGS()->addEnchantment($enchant);
		$boots = VanillaItems::GOLDEN_BOOTS()->addEnchantment($enchant);

		$armor = $entity->getArmorInventory();
		$armor->setChestplate($chest_plate);
		$armor->setLeggings($leggings);
		$armor->setBoots($boots);

		$entity->getInventory()->addItem($item);
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§l§cHCF", Entity::HCF, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
		$this->floating_text("§eClick to join HCF.", Entity::HCF . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function npc_kitmap(GhostlyPlayer $player): void
	{
		$nbt = new CompoundTag();
		$nbt->setString("npcId", Entity::KITMAP);
		$nbt->setString("server_name", "KITMAP");

		$location = $player->getLocation();
		$skin = $player->getSkin();

		$this->remove_entity(Entity::KITMAP);

		$entity = new HumanType($location, $player->getSkin(), $nbt);
		$entity->setNameTag("§7Players: §f??§7/§f??");

		$enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);

		$item = VanillaItems::IRON_PICKAXE()->addEnchantment($enchant);
		$chest_plate = VanillaItems::GOLDEN_CHESTPLATE()->addEnchantment($enchant);
		$leggings = VanillaItems::GOLDEN_LEGGINGS()->addEnchantment($enchant);
		$boots = VanillaItems::GOLDEN_BOOTS()->addEnchantment($enchant);

		$armor = $entity->getArmorInventory();
		$armor->setChestplate($chest_plate);
		$armor->setLeggings($leggings);
		$armor->setBoots($boots);

		$entity->getInventory()->addItem($item);
		$entity->spawnToAll();

		$eLocation = $entity->getLocation();

		$x = $eLocation->getX();
		$y = $eLocation->getY();
		$z = $eLocation->getZ();

		$this->floating_text("§l§cKitMap", Entity::KITMAP, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
		$this->floating_text("§eClick to join KitMap.", Entity::KITMAP . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
	}

	public function purge_all(): void
	{
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof GhostlyPlayer) {
					continue;
				}

				$entity->kill();
			}
		}
	}

	private int $count = 0;

	public function update_server_status(): void
	{
		$lobby = Lobby::getInstance();
		$world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();
		$offline = ["§cOffline", "§cOffline.", "§cOffline..", "§cOffline..."];

		if (!isset($world)) {
			return;
		}

		if ($this->count > 3) {
			$this->count = 0;
		}

		foreach ($world->getEntities() as $entity) {
			if (!$entity instanceof HumanType) {
				continue;
			}
			switch ($entity->getNpcId()) {
				case Entity::COMBO:
					$server = ServerManager::getInstance()->getServerByName("Combo");

					if ($server === null) {
						$entity->setNameTag("§k§6!!§r§cCOMING SOON§k§6!!");
						$this->kill_text(Entity::COMBO . Entity::EXTRA);
						break;
					}

					if (!$server->isOnline()) {
						$entity->setNameTag($offline[$this->count]);
						$this->kill_text(Entity::COMBO . Entity::EXTRA);
						break;
					}

					$entity->setNameTag("§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}");

					if (!$this->exist_text(Entity::COMBO . Entity::EXTRA)) {
						$location = $entity->getLocation();

						if ($server->isWhitelist()) {
							$this->floating_text("§c" . "WHITELISTED", Entity::COMBO . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
							break;
						}
						$this->floating_text("§eClick to join Combo.", Entity::COMBO . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
						break;
					}

					if ($server->isWhitelist()) {
						$this->update_text("§c" . "WHITELISTED", Entity::COMBO . Entity::EXTRA);
					} else {
						$this->update_text("§eClick to join Combo.", Entity::COMBO . Entity::EXTRA);
					}
					break;
				case Entity::PRACTICE:
					$server = ServerManager::getInstance()->getServerByName("Practice");

					if ($server === null) {
						$entity->setNameTag("§k§6!!§r§cCOMING SOON§k§6!!");
						$this->kill_text(Entity::PRACTICE . Entity::EXTRA);
						break;
					}

					if (!$server->isOnline()) {
						$entity->setNameTag($offline[$this->count]);
						$this->kill_text(Entity::PRACTICE . Entity::EXTRA);
						break;
					}

					$entity->setNameTag("§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}");

					if (!$this->exist_text(Entity::PRACTICE . Entity::EXTRA)) {
						$location = $entity->getLocation();

						if ($server->isWhitelist()) {
							$this->floating_text("§c" . "WHITELISTED", Entity::PRACTICE . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
							break;
						}
						$this->floating_text("§eClick to join Combo.", Entity::PRACTICE . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
						break;
					}

					if ($server->isWhitelist()) {
						$this->update_text("§c" . "WHITELISTED", Entity::PRACTICE . Entity::EXTRA);
					} else {
						$this->update_text("§eClick to join Combo.", Entity::PRACTICE . Entity::EXTRA);
					}
					break;
				case Entity::KITMAP:
					$server = ServerManager::getInstance()->getServerByName("KITMAP");

					if ($server === null) {
						$entity->setNameTag("§k§6!!§r§cCOMING SOON§k§6!!");
						$this->kill_text(Entity::KITMAP . Entity::EXTRA);
						break;
					}

					if (!$server->isOnline()) {
						$entity->setNameTag($offline[$this->count]);
						$this->kill_text(Entity::KITMAP . Entity::EXTRA);
						break;
					}
					$entity->setNameTag("§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}");

					if (!$this->exist_text(Entity::KITMAP . Entity::EXTRA)) {
						$location = $entity->getLocation();

						if ($server->isWhitelist()) {
							$this->floating_text("§c" . "WHITELISTED", Entity::KITMAP . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
							break;
						}
						$this->floating_text("§eClick to join Combo.", Entity::KITMAP . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
						break;
					}

					if ($server->isWhitelist()) {
						$this->update_text("§c" . "WHITELISTED", Entity::KITMAP . Entity::EXTRA);
					} else {
						$this->update_text("§eClick to join Combo.", Entity::KITMAP . Entity::EXTRA);
					}
					break;
				case Entity::HCF:
					$server = ServerManager::getInstance()->getServerByName("HCF");

					if ($server === null) {
						$entity->setNameTag("§k§6!!§r§cCOMING SOON§k§6!!");
						$this->kill_text(Entity::HCF . Entity::EXTRA);
						break;
					}

					if (!$server->isOnline()) {
						$entity->setNameTag($offline[$this->count]);
						$this->kill_text(Entity::HCF . Entity::EXTRA);
						break;
					}

					$entity->setNameTag("§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}");

					if (!$this->exist_text(Entity::HCF . Entity::EXTRA)) {
						$location = $entity->getLocation();

						if ($server->isWhitelist()) {
							$this->floating_text("§c" . "WHITELISTED", Entity::HCF . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
							break;
						}
						$this->floating_text("§eClick to join Combo.", Entity::HCF . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
						break;
					}

					if ($server->isWhitelist()) {
						$this->update_text("§c" . "WHITELISTED", Entity::HCF . Entity::EXTRA);
					} else {
						$this->update_text("§eClick to join Combo.", Entity::HCF . Entity::EXTRA);
					}
					break;
				case Entity::UHC:
					$server = ServerManager::getInstance()->getServerByName("UHC");

					if ($server === null) {
						$entity->setNameTag("§k§6!!§r§cCOMING SOON§k§6!!");
						$this->kill_text(Entity::UHC . Entity::EXTRA);
						break;
					}

					if (!$server->isOnline()) {
						$entity->setNameTag($offline[$this->count]);
						$this->kill_text(Entity::UHC . Entity::EXTRA);
						break;
					}

					$entity->setNameTag("§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}");

					if (!$this->exist_text(Entity::UHC . Entity::EXTRA)) {
						$location = $entity->getLocation();

						if ($server->isWhitelist()) {
							$this->floating_text("§c" . "WHITELISTED", Entity::UHC . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
							break;
						}
						$this->floating_text("§eClick to join Combo.", Entity::UHC . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
						break;
					}

					if ($server->isWhitelist()) {
						$this->update_text("§c" . "WHITELISTED", Entity::UHC . Entity::EXTRA);
					} else {
						$this->update_text("§eClick to join Combo.", Entity::UHC . Entity::EXTRA);
					}
					break;
				case Entity::UHC_RUN:
					$server = ServerManager::getInstance()->getServerByName("UHC_RUN");

					if ($server === null) {
						$entity->setNameTag("§k§6!!§r§cCOMING SOON§k§6!!");
						$this->kill_text(Entity::UHC_RUN . Entity::EXTRA);
						break;
					}

					if (!$server->isOnline()) {
						$entity->setNameTag($offline[$this->count]);
						$this->kill_text(Entity::UHC_RUN . Entity::EXTRA);
						break;
					}

					$entity->setNameTag("§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}");

					if (!$this->exist_text(Entity::UHC_RUN . Entity::EXTRA)) {
						$location = $entity->getLocation();

						if ($server->isWhitelist()) {
							$this->floating_text("§c" . "WHITELISTED", Entity::UHC_RUN . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
							break;
						}
						$this->floating_text("§eClick to join Combo.", Entity::UHC_RUN . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
						break;
					}

					if ($server->isWhitelist()) {
						$this->update_text("§c" . "WHITELISTED", Entity::UHC_RUN . Entity::EXTRA);
					} else {
						$this->update_text("§eClick to join Combo.", Entity::UHC_RUN . Entity::EXTRA);
					}
					break;
			}
		}

		$this->count++;
	}

	public function kill_text(string $textId): void
	{
		$lobby = Lobby::getInstance();
		$world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();

		if (!isset($world)) {
			return;
		}

		foreach ($world->getEntities() as $entity) {
			if (!$entity instanceof FloatingTextType || $entity->getTextId() !== $textId) {
				continue;
			}

			$entity->kill();
		}
	}

	public function exist_text(string $textId): bool
	{
		$lobby = Lobby::getInstance();
		$world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();

		if (!isset($world)) {
			return false;
		}

		foreach ($world->getEntities() as $entity) {
			if (!$entity instanceof FloatingTextType || $entity->getTextId() !== $textId) {
				continue;
			}

			return true;
		}

		return false;
	}

	public function update_text(string $text, string $textId): void
	{
		$lobby = Lobby::getInstance();
		$world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();

		if (!isset($world)) {
			return;
		}

		foreach ($world->getEntities() as $entity) {
			if (!$entity instanceof FloatingTextType || $entity->getTextId() !== $textId) {
				continue;
			}

			$entity->setNameTag($text);
		}
	}
}