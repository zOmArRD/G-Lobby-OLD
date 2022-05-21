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

namespace zomarrd\ghostly\lobby\events;

use GhostlyMC\DatabaseAPI\mysql\MySQL;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\GameMode;
use pocketmine\player\XboxLivePlayerInfo;
use zomarrd\ghostly\lobby\database\mysql\queries\InsertQuery;
use zomarrd\ghostly\lobby\database\mysql\queries\SelectQuery;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\player\DeviceData;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\player\permission\PermissionKey;
use zomarrd\ghostly\lobby\security\ChatHandler;
use zomarrd\ghostly\lobby\world\Lobby;

final class PlayerListener implements Listener
{
    private array $globalMuteAlertDelay;

    public function onCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(GhostlyPlayer::class);
    }

    public function PlayerPreLoginEvent(PlayerPreLoginEvent $event): void
    {
        $info = $event->getPlayerInfo();

        if (!$info instanceof XboxLivePlayerInfo) {
            return;
        }

        $xuid = $info->getXuid();
        $name = $info->getUsername();
        $locale = $info->getLocale();

        DeviceData::saveUIProfile($name, $info->getExtraData()['UIProfile']);

        MySQL::runAsync(new SelectQuery("SELECT * FROM ghostly_playerdata WHERE xuid = '$xuid';"), static function($result) use ($xuid, $name, $locale): void {
            if (count($result) === 0) {
                MySQL::runAsync(new InsertQuery(sprintf("INSERT INTO ghostly_playerdata(xuid, username, language, scoreboard) VALUES ('%s', '%s', '%s', true);", $xuid, $name, $locale)));
            }
        });
    }

    public function PlayerLoginEvent(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();

        if (!$player instanceof GhostlyPlayer) {
            return;
        }

        $xuid = $player->getXuid();

        MySQL::runAsync(new SelectQuery("SELECT * FROM ghostly_playerdata WHERE xuid = '$xuid';"), static function($result) use ($player): void {
            if (count($result) === 0) {
                $player->transfer('ghostlymc.live');
                return;
            }

            $data = $result[0];
            $player->setVisibilityMode($data->visibilityMode);
            $player->setLanguage($data->language);
            $player->setScoreboard((bool)$data->scoreboard);
        });
    }

    public function PlayerQuitEvent(PlayerQuitEvent $event): void
    {
        $event->setQuitMessage('');
        $player = $event->getPlayer();

        if ($player instanceof GhostlyPlayer) {
            $player->quitQueue();
            $player->teleport_to_lobby();
        }
    }

    public function PlayerMoveEvent(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof GhostlyPlayer) {
            return;
        }

        $lobby = Lobby::getInstance();

        if (is_null($lobby)) {
            return;
        }

        $motion = $player->getMotion();
        $location = $player->getLocation();

        if ($lobby->getWorld()->getBlock($player->getLocation()->floor()->subtract(0, 1, 0))->getId() === BlockLegacyIds::SLIME) {
            $x = -sin($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI);
            $z = cos($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI);
            $player->setMotion($motion->add($x * 2, 1.20, $z * 2));
            $player->sendSound(LevelSoundEvent::LAUNCH);
        }

        if ($location->getY() <= $lobby->getMinVoid()) {
            $player->teleport_to_lobby();
        }
    }

    public function PlayerExhaustEvent(PlayerExhaustEvent $event): void
    {
        $event->cancel();
    }

    public function PlayerToggleFlightEvent(PlayerToggleFlightEvent $event): void
    {
        $player = $event->getPlayer();
        $location = $player->getLocation();
        $motion = $player->getMotion();
        if (!$player instanceof GhostlyPlayer) {
            return;
        }

        if ($player->getGamemode() === GameMode::CREATIVE()) {
            return;
        }

        $event->cancel();
        $player->setMotion($motion->add(-sin($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI), $motion->y + 0.75, cos($location->yaw / 180 * M_PI) * cos($location->pitch / 180 * M_PI)));

        $player->sendSound(LevelEvent::SOUND_BLAZE_SHOOT, 'level-event');
    }

    /**
     * Create a cool-down for the chat
     *
     * @todo Create the filters in the proxy, for better performance.
     */
    public function PlayerChatEvent(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $player_name = $player->getName();
        $global_mute_delay = 2;

        if (!$player instanceof GhostlyPlayer) {
            return;
        }

        foreach (ChatHandler::$filter->get('domains') as $domain) {
            if (strpos($event->getMessage(), $domain)) {
                $event->cancel();

                foreach (ChatHandler::$filter->get('allowedIps') as $allowed) {
                    if (strpos($event->getMessage(), $allowed)) {
                        $event->uncancel();
                        return;
                    }
                }

                $player->sendMessage(PREFIX . '§cYou have tried to send an ip address, which is not allowed in our network, be careful, you can be sanctioned!');
            }
        }

        $event->setMessage(ChatHandler::getUncensoredMessage($event->getMessage()));

        /* global mute stuff */
        if (!Ghostly::isGlobalMute() || ($player->hasPermission(PermissionKey::GHOSTLY_GLOBAL_MUTE_BYPASS) && $player->isOp())) {
            return;
        }

        $event->cancel();

        if (!isset($this->globalMuteAlertDelay[$player_name]) || time() - $this->globalMuteAlertDelay[$player_name] >= $global_mute_delay) {
            $player->sendTranslated(LangKey::GLOBAL_MUTE_IS_ENABLED);
            $this->globalMuteAlertDelay[$player_name] = time();
        }
        /** end */
    }

    public function LeavesDecayEvent(LeavesDecayEvent $event): void
    {
        $event->cancel();
    }

    public function EntityDamageEvent(EntityDamageEvent $event): void
    {
        $event->cancel();
    }

    public function BlockBurnEvent(BlockBurnEvent $event): void
    {
        $event->cancel();
    }

    public function DataPacketSendEvent(DataPacketSendEvent $event): void
    {
        $packets = $event->getPackets();
        foreach ($packets as $packet) {
            if ($packet instanceof AvailableCommandsPacket) {
                $targets = $event->getTargets();

                foreach ($targets as $target) {
                    if ($target->getPlayer() !== null && $target->getPlayer()->getName() !== 'X6JGT') {
                        $packet->commandData = array_intersect_key($packet->commandData, ['help']);
                    }
                }
            }
        }
    }

    public function BlockSpreadEvent(BlockSpreadEvent $event): void
    {
        $event->cancel();
    }

    public function BlockGrowEvent(BlockGrowEvent $event): void
    {
        $event->cancel();
    }

    public function BlockFormEvent(BlockFormEvent $event): void
    {
        $event->cancel();
    }
}