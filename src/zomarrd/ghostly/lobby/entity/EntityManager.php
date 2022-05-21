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

namespace zomarrd\ghostly\lobby\entity;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\world\World;
use RuntimeException;
use zomarrd\ghostly\lobby\entity\type\FloatingTextType;
use zomarrd\ghostly\lobby\entity\type\HumanType;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\server\ServerList;
use zomarrd\ghostly\lobby\server\ServerManager;
use zomarrd\ghostly\lobby\world\Lobby;

final class EntityManager
{
    public static int $count = 0;

    public function register(): void
    {
        EntityFactory::getInstance()->register(HumanType::class, function(World $world, CompoundTag $tag): HumanType {
            return new HumanType(EntityDataHelper::parseLocation($tag, $world), HumanType::parseSkinNBT($tag), $tag);
        }, ['HumanType']);

        EntityFactory::getInstance()->register(FloatingTextType::class, function(World $world, CompoundTag $tag): FloatingTextType {
            return new FloatingTextType(EntityDataHelper::parseLocation($tag, $world), $tag);
        }, ['FloatingTextType']);
    }

    public function getCompoundTag(string $npcId): CompoundTag
    {
        return (new CompoundTag())->setString('npcId', $npcId);
    }


    public function entity_discord(GhostlyPlayer $player): void
    {
        $entity = $this->create($player, Entity::DISCORD);

        $eLocation = $entity->getLocation();
        $x = $eLocation->getX();
        $y = $eLocation->getY();
        $z = $eLocation->getZ();

        foreach (
            [
                '§l§9Discord Server' => 3.20,
                'Join our Discord community to keep' => 2.90,
                'up-to-date about recent updates,' => 2.60,
                'giveaways, suggest us ideas or' => 2.30,
                'appeal a punishment.' => 2.00,
                '§eClick to view Discord.' => 1.40
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
            if (($entity instanceof HumanType) && $entity->getNpcId() === $npcId) {
                $entity->close();
                $this->kill_text($npcId);
                $this->kill_text($npcId . Entity::EXTRA);
            }
        }
    }

    public function kill_text(string $textId): void
    {
        $lobby = Lobby::getInstance();
        $world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();

        if (!isset($world)) {
            return;
        }

        foreach ($world->getEntities() as $entity) {
            if (($entity instanceof FloatingTextType) && $entity->getTextId() === $textId) {
                $entity->close();
            }
        }
    }

    public function create(GhostlyPlayer $player, string $npcId): HumanType
    {
        $this->remove_entity($npcId);
        $entity = new HumanType($player->getLocation(), $player->getSkin(), $this->getCompoundTag($npcId));
        $entity->setNameTag('');
        $entity->setNameTagVisible(false);
        $entity->setNameTagAlwaysVisible(false);
        $entity->spawnToAll();
        return $entity;
    }

    public function floating_text(string $text, string $id, Location $location, bool $spawnToAll = true, GhostlyPlayer $player = null): void
    {
        $nbt = new CompoundTag();
        $nbt->setString('TextId', $id);

        $entity = new FloatingTextType($location, $nbt);
        $entity->setNameTag('§r§7' . $text);

        if ($spawnToAll) {
            $entity->spawnToAll();
            return;
        }

        if (!isset($player)) {
            throw new RuntimeException('Player is NULL');
        }

        $entity->spawnTo($player);
    }

    public function entity_store(GhostlyPlayer $player): void
    {
        $entity = $this->create($player, Entity::STORE);
        $eLocation = $entity->getLocation();

        $x = $eLocation->getX();
        $y = $eLocation->getY();
        $z = $eLocation->getZ();

        foreach (
            [
                '§l§aStore' => 2.60,
                'You can purchase G-Coins, ranks' => 2.30,
                'and tags on our store!.' => 2.00,
                '§eClick to view store.' => 1.40
            ] as $text => $mY) {
            $this->floating_text($text, Entity::STORE, new Location($x, $y + $mY, $z, $eLocation->getWorld(), 0.0, 0.0));
        }
    }

    public function spawnHuman(GhostlyPlayer $player, string $npcId, array $texts): void
    {
        $entity = $this->create($player, $npcId);

        $eLocation = $entity->getLocation();

        $x = $eLocation->getX();
        $y = $eLocation->getY();
        $z = $eLocation->getZ();

        $textPos = 1.50;
        foreach ($texts as $text) {
            if ($text === '{LINE}') {
                $textPos += 0.30;
                continue;
            }

            $this->floating_text($text, $npcId, new Location($x, $y + $textPos, $z, $eLocation->getWorld(), 0.0, 0.0));
            $textPos += 0.30;
        }
    }

    public function createEntityServer(GhostlyPlayer $player, string $server): void
    {
        $entity = $this->create($player, $server);
        $entity->setNameTag('§7Players: §f??§7/§f??');

        $eLocation = $entity->getLocation();

        $x = $eLocation->getX();
        $y = $eLocation->getY();
        $z = $eLocation->getZ();

        switch ($server) {
            case ServerList::PRACTICE:
                $this->setEntityItems(VanillaItems::DIAMOND_SWORD(), $entity);
                break;
            case ServerList::COMBO:
                $this->setEntityItems(VanillaItems::ENDER_PEARL(), $entity);
                break;
            case ServerList::HCF:
                $this->setEntityItems(VanillaItems::DIAMOND_PICKAXE(), $entity);
                break;
            case ServerList::UHCRUN:
                $this->setEntityItems(VanillaItems::APPLE(), $entity);
                break;
            case ServerList::UHC:
                $this->setEntityItems(VanillaItems::GOLDEN_APPLE(), $entity);
                break;
            case ServerList::KITMAP:
                $this->setEntityItems(VanillaItems::GOLDEN_PICKAXE(), $entity);
                break;
        }

        $entity->spawnToAll();
        $this->floating_text('§l§c' . $server, $server, new Location($x, $y + 2.10, $z, $eLocation->getWorld(), 0.0, 0.0));
        $this->floating_text('§eClick to join ' . $server . '.', $server . Entity::EXTRA, new Location($x, $y + 1.50, $z, $eLocation->getWorld(), 0.0, 0.0));
    }

    public function setEntityItems(Item $handItem, HumanType $entity): void
    {
        $enchant = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);
        $chest_plate = VanillaItems::DIAMOND_CHESTPLATE()->addEnchantment($enchant);
        $leggings = VanillaItems::DIAMOND_LEGGINGS()->addEnchantment($enchant);
        $boots = VanillaItems::DIAMOND_BOOTS()->addEnchantment($enchant);

        $armor = $entity->getArmorInventory();
        $armor->setChestplate($chest_plate);
        $armor->setLeggings($leggings);
        $armor->setBoots($boots);

        $entity->getInventory()->addItem($handItem);
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

    public function update_server_status(string $serverName, int $count): void
    {
        $lobby = Lobby::getInstance();
        $world = isset($lobby) ? $lobby->getWorld() : Server::getInstance()->getWorldManager()->getDefaultWorld();
        $offline = ['§cOffline.', '§cOffline..', '§cOffline...'];

        if (!isset($world)) {
            return;
        }

        foreach ($world->getEntities() as $entity) {
            if (!$entity instanceof HumanType) {
                continue;
            }

            if ($entity->getNpcId() === $serverName) {
                $server = ServerManager::getInstance()->getServerByName($serverName);

                if ($server === null) {
                    $entity->setNameTag('§k§6!!§r§cSOON§k§6!!');
                    $this->kill_text($serverName . Entity::EXTRA);
                    break;
                }

                if (!$server->isOnline()) {
                    $entity->setNameTag($offline[$count]);
                    $this->kill_text($serverName . Entity::EXTRA);
                    break;
                }

                $entity->setNameTag(sprintf('§7Players: §f%s§7/§f%s', $server->getOnlinePlayers(), $server->getMaxPlayers()));

                if (!$this->exist_text($serverName . Entity::EXTRA)) {
                    $location = $entity->getLocation();

                    if ($server->isWhitelisted()) {
                        $this->floating_text('§cWHITELISTED', $serverName . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
                        break;
                    }

                    $this->floating_text('§eClick to join ' . $serverName . '.', $serverName . Entity::EXTRA, new Location($location->x, $location->y + 1.50, $location->z, $location->getWorld(), 0.0, 0.0));
                    break;
                }

                if ($server->isWhitelisted()) {
                    $this->update_text('§cWHITELISTED', $serverName . Entity::EXTRA);
                } else {
                    $this->update_text('§eClick to join ' . $serverName . '.', $serverName . Entity::EXTRA);
                }
            }
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