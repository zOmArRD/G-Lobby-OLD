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

namespace zomarrd\ghostly\lobby\player;

use GhostlyMC\GCoinsAPI\Balance;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\UIProfile;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\Server as PMServer;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use zomarrd\ghostly\lobby\config\ConfigManager;
use zomarrd\ghostly\lobby\extensions\scoreboard\Scoreboard;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\network\proxy\AntiProxy;
use zomarrd\ghostly\lobby\player\item\ItemManager;
use zomarrd\ghostly\lobby\player\language\LangHandler;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\player\language\Language;
use zomarrd\ghostly\lobby\player\permission\PermissionKey;
use zomarrd\ghostly\lobby\server\queue\Queue;
use zomarrd\ghostly\lobby\server\Server;
use zomarrd\ghostly\lobby\server\ServerManager;
use zomarrd\ghostly\lobby\utils\VISIBILITY;
use zomarrd\ghostly\lobby\world\Lobby;

final class GhostlyPlayer extends Player
{
    public int|float $messageReceivedDelay = 0;
    public ?Queue $queue = null;
    private string $currentLang = Language::ENGLISH_US;
    private bool $loaded = false, $scoreboard = true, $canInteractItem = true;
    private Scoreboard $scoreboard_session;
    private ItemManager $itemManager;
    private Cooldown $cooldown;
    private ?Balance $balanceAPI = null;
    private int $visibilityMode = VISIBILITY::ALL;

    public function __construct
    (
        PMServer       $server,
        NetworkSession $session,
        PlayerInfo     $playerInfo,
        bool           $authenticated,
        Location       $spawnLocation,
        ?CompoundTag   $namedtag
    ) {
        parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);
        $this->cooldown = new Cooldown();
    }

    public function getVisibilityMode(): string
    {
        return match ($this->visibilityMode) {
            VISIBILITY::STAFF => LangKey::ITEM_VISIBILITY_STAFF,
            VISIBILITY::NOBODY => LangKey::ITEM_VISIBILITY_NONE,
            default => LangKey::ITEM_VISIBILITY_ALL,
        };
    }

    public function setVisibilityMode(int $visibilityMode): void
    {
        $this->visibilityMode = $visibilityMode;

        $message = match ($visibilityMode) {
            VISIBILITY::STAFF => "Staff",
            VISIBILITY::NOBODY => "Nobody",
            default => "All",
        };

        $this->hide($visibilityMode);
        $this->sendTranslated(LangKey::PLAYER_VISIBILITY_UPDATED, ["{VISIBILITY-MODE}" => $message]);
    }

    public function hide(int $mode): void
    {
        switch ($mode) {
            case VISIBILITY::STAFF:
                foreach ($this->server->getOnlinePlayers() as $player) {
                    if ($player->hasPermission('ghostly.staff')) {
                        continue;
                    }

                    $this->hidePlayer($player);
                }
                break;
            case VISIBILITY::NOBODY:
                foreach ($this->server->getOnlinePlayers() as $player) {
                    $this->hidePlayer($player);
                }
                break;
            case VISIBILITY::ALL:
                foreach ($this->server->getOnlinePlayers() as $player) {
                    $this->showPlayer($player);
                }
                break;
        }
    }

    public function getBalanceAPI(): ?Balance
    {
        return $this->balanceAPI;
    }

    public function setBalanceAPI(Balance $balanceAPI): void
    {
        $this->balanceAPI = $balanceAPI;
    }

    public function getQueue(): ?Queue
    {
        return $this->queue;
    }

    public function setQueue(string|Server $server): Queue
    {
        return $this->queue = new Queue($this, $server);
    }

    public function isQueue(): bool
    {
        return isset($this->queue);
    }

    public function quitQueue(): void
    {
        $this->queue = null;
    }

    public function canInteractItem(): bool
    {
        return $this->canInteractItem;
    }

    public function isScoreboard(): bool
    {
        return $this->scoreboard;
    }

    public function hasClassicProfile(): bool
    {
        return $this->getUIProfile() === UIProfile::CLASSIC;
    }

    public function getUIProfile(): int
    {
        return DeviceData::getUIProfile($this->getName());
    }

    public function hasDifferentLocale(): bool
    {
        return !$this->getLang(true)->equals($this->getLang());
    }

    public function getLang(bool $fromLocale = false): Language
    {
        if ($fromLocale === true) {
            return getLanguage($this->locale);
        }

        return getLanguage($this->currentLang);
    }

    public function onUpdate(int $currentTick): bool
    {
        if (!$this->isLoaded()) {
            $this->scoreboard_session = new Scoreboard($this);
            $this->itemManager = new ItemManager($this);
            $this->setLoaded();
        }

        if ($currentTick % 5 === 0) {
            $this->getScoreboardSession()->setScoreboard();
        }

        return parent::onUpdate($currentTick);
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    private function setLoaded(): void
    {
        $this->loaded = true;
    }

    public function setScoreboard(bool $scoreboard): void
    {
        $this->scoreboard = $scoreboard;
    }

    public function getScoreboardSession(): Scoreboard
    {
        return $this->scoreboard_session;
    }

    public function setLanguage(string $lang): void
    {
        $this->currentLang = $lang;
    }

    public function getQueueItem(): void
    {
        $this->getInventory()?->clearAll();
        $this->setItem(8, $this->getItemManager()->get(LangKey::ITEM_QUEUE_EXIT));
    }

    private function setItem(int $index, Item $item): void
    {
        $this->getInventory()?->setItem($index, $item);
    }

    public function getItemManager(): ItemManager
    {
        return $this->itemManager;
    }

    public function closeInventory(): void
    {
        $this->sendSound(LevelSoundEvent::DROP_SLOT);
        $session = InvMenuHandler::getPlayerManager()->get($this);
        $session->removeCurrentMenu();
    }

    public function sendSound(int $sound, string $type = 'level-sound'): void
    {
        switch ($type) {
            case 'level-event':
                $this->getNetworkSession()->sendDataPacket(LevelEventPacket::create($sound, 0, $this->getLocation()->asVector3()));
                break;
            case 'level-sound':
                $this->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::create($sound, $this->getLocation()->asVector3(), -1, ':', false, false));
                break;
        }
    }

    public function transferTo(string|Server $server): void
    {

        if (!$this->isOnline()) {
            return;
        }

        if (is_string($server)) {
            $name = $server;
            $server = ServerManager::getInstance()->getServerByName($server);
        }

        if (is_null($server)) {
            $this->sendTranslated(LangKey::SERVER_TRANSFER_FAILED, ['{SERVER-NAME}' => $name ?? '']);
            $this->sendSound(LevelSoundEvent::EXPLODE);
            $this->sendTranslated(LangKey::SERVER_NOT_AVAILABLE);
            $this->setCanInteractItem();
            $this->getLobbyItems();
            return;
        }

        if ($server->getName() === Server['name']) {
            $this->sendTranslated(LangKey::SERVER_TRANSFER_FAILED, ['{SERVER-NAME}' => $server->getName()]);
            $this->sendSound(LevelSoundEvent::RANDOM_ANVIL_USE);
            $this->sendTranslated(LangKey::SERVER_ALREADY_CONNECTED);
            $this->setCanInteractItem();
            $this->getLobbyItems();
            return;
        }

        if (!$server->isOnline()) {
            $this->sendTranslated(LangKey::SERVER_TRANSFER_FAILED, ['{SERVER-NAME}' => $server->getName()]);
            $this->sendSound(LevelSoundEvent::RANDOM_ANVIL_USE);
            $this->sendTranslated(LangKey::SERVER_NOT_ONLINE);
            $this->setCanInteractItem();
            $this->getLobbyItems();
            return;
        }

        if ($server->isWhitelisted() && !$this->hasPermission(PermissionKey::GHOSTLY_SERVER_CONNECT_WHITELISTED)) {
            $this->sendTranslated(LangKey::SERVER_TRANSFER_FAILED, ['{SERVER-NAME}' => $server->getName()]);
            $this->sendSound(LevelSoundEvent::RANDOM_ANVIL_USE);
            $this->sendTranslated(LangKey::SERVER_IS_WHITELISTED);
            $this->setCanInteractItem();
            $this->getLobbyItems();
            return;
        }

        if (!$this->hasPermission(PermissionKey::GHOSTLY_SERVER_JOIN_BYPASS) && $server->getOnlinePlayers() >= $server->getMaxPlayers()) {
            $this->sendTranslated(LangKey::SERVER_TRANSFER_FAILED, ['{SERVER-NAME}' => $server->getName()]);
            $this->sendSound(LevelEvent::SOUND_SHOOT, 'level-event');
            $this->sendTranslated(LangKey::SERVER_IS_FULL);
            $this->setCanInteractItem();
            $this->getLobbyItems();
            return;
        }

        $this->sendTranslated(LangKey::SERVER_CONNECTED, ['{SERVER-NAME}' => $server->getName()]);
        $this->sendTranslated(LangKey::SERVER_TRANSFER_PROCESSING, ['{SERVER-NAME}' => $server->getName()]);
        $this->transfer($server->getName(), 0, "Transfer to {$server->getName()}");
    }

    public function sendTranslated(string $string, array $replaceable = []): void
    {
        $this->sendMessage($this->getTranslation($string, $replaceable));
    }

    public function getTranslation(string $string, array $replaceable = []): string
    {
        return $this->getLang()->getStrings($string, $replaceable);
    }

    public function setCanInteractItem(bool $canInteractItem = true): void
    {
        $this->canInteractItem = $canInteractItem;
    }

    public function getLobbyItems(): void
    {
        $this->getInventory()?->clearAll();

        foreach (
            [
                LangKey::ITEM_SERVER_SELECTOR => 0,
                LangKey::ITEM_COSMETICS_SELECTOR => 4,
                $this->getVisibilityMode() => 6,
                LangKey::ITEM_PLAYER_SETTINGS => 7,
                LangKey::ITEM_LOBBY_SELECTOR => 8
            ] as $item => $index) {
            $this->setItem($index, $this->getItemManager()->get($item));
        }
    }

    public function hasCooldown(float|int $time): bool
    {
        return $this->cooldown->hasCooldown($time);
    }

    public function setCooldown(): void
    {
        $this->cooldown->setCooldown();
    }

    public function getMessageReceivedDelay(float|int $time): bool
    {
        return time() - $this->messageReceivedDelay < $time;
    }

    public function setMessageReceivedDelay(): void
    {
        $this->messageReceivedDelay = time();
    }

    public function attackEntity(Entity $entity): bool
    {
        if (!$entity->isAlive()) {
            return false;
        }

        if ($entity instanceof ItemEntity || $entity instanceof Arrow) {
            return false;
        }

        $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
        return true;
    }

    public function getLeaveMessage(): string
    {
        return '';
    }

    public function doFirstSpawn(): void
    {
        if ($this->spawned) {
            return;
        }

        $this->spawned = true;
        $this->recheckBroadcastPermissions();
        $this->getPermissionRecalculationCallbacks()->add(function(array $changedPermissionsOldValues): void {
            if (isset($changedPermissionsOldValues[PMServer::BROADCAST_CHANNEL_ADMINISTRATIVE]) || isset($changedPermissionsOldValues[PMServer::BROADCAST_CHANNEL_USERS])) {
                $this->recheckBroadcastPermissions();
            }
        });

        $this->setMovementSpeed($this->getMovementSpeed() * 1.4);
        $this->onJoin();

        if (ConfigManager::getServerConfig()->get('proxy_detect')) {
            PMServer::getInstance()->getAsyncPool()->submitTask(new AntiProxy($this->getName(), $this->getNetworkSession()->getIp()));
        }

        $this->noDamageTicks = 60;

        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof self) {
                switch ($player->getVisibilityMode()) {
                    case VISIBILITY::ALL:
                        $this->spawnTo($player);
                        break;
                    case VISIBILITY::STAFF:
                        if ($this->hasPermission('STAFF PERMISSION HERE')) {
                            $this->spawnTo($player);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
    }

    private function recheckBroadcastPermissions(): void
    {
        foreach (
            [
                DefaultPermissionNames::BROADCAST_ADMIN => PMServer::BROADCAST_CHANNEL_ADMINISTRATIVE,
                DefaultPermissionNames::BROADCAST_USER => PMServer::BROADCAST_CHANNEL_USERS
            ] as $permission => $channel) {
            if ($this->hasPermission($permission)) {
                $this->server->subscribeToBroadcastChannel($channel, $this);
            } else {
                $this->server->unsubscribeFromBroadcastChannel($channel, $this);
            }
        }
    }

    public function onJoin(): void
    {
        $this->getLobbyItems();
        $this->setGamemode(GameMode::SURVIVAL());
        $this->setHealth(20);
        $this->getHungerManager()->setFood(20);
        $this->setAllowFlight(true);
        $this->setMovementSpeed($this->getMovementSpeed() * 1.2);
        $this->teleport_to_lobby();
    }

    public function teleport_to_lobby(): void
    {
        $lobby = Lobby::getInstance();

        if ($lobby !== null) {
            $this->teleport($lobby->getSpawnPosition(), $lobby->getSpawnYaw(), $lobby->getSpawnPitch());
        }
    }

    public function chat(string $message): bool
    {
        $this->removeCurrentWindow();

        $message = TextFormat::clean($message, false);
        foreach (explode("\n", $message) as $messagePart) {
            if (($this->messageCounter-- > 0) && trim($messagePart) !== '' && strlen($messagePart) <= 255) {
                if (str_starts_with($messagePart, './')) {
                    $messagePart = substr($messagePart, 1);
                }

                $ev = new PlayerCommandPreprocessEvent($this, $messagePart);
                $ev->call();

                if ($ev->isCancelled()) {
                    break;
                }

                if (str_starts_with($ev->getMessage(), '/')) {
                    Timings::$playerCommand->startTiming();
                    $this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
                    Timings::$playerCommand->stopTiming();
                } else {
                    $ev = new PlayerChatEvent($this, $ev->getMessage(), $this->server->getBroadcastChannelSubscribers(PMServer::BROADCAST_CHANNEL_USERS));
                    $ev->call();
                    /** TODO: Put the player's rank */
                    if (!$ev->isCancelled()) {
                        $this->server->broadcastMessage(TextFormat::WHITE . $this->getDisplayName() . "§r§7: {$ev->getMessage()}", $ev->getRecipients());
                    }
                }
            }
        }

        return true;
    }

    public function breakBlock(Vector3 $pos): bool
    {
        if (!$this->isOp()) {
            return false;
        }
        return parent::breakBlock($pos);
    }

    public function isOp(): bool
    {
        return Ghostly::getInstance()->getServer()->isOp($this->getName());
    }

    public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset): bool
    {
        if (!$this->isOp()) {
            return false;
        }

        return parent::interactBlock($pos, $face, $clickOffset);
    }

    protected function internalSetGameMode(GameMode $gameMode): void
    {
        $this->gamemode = $gameMode;

        $this->allowFlight = true;
        $this->hungerManager->setEnabled(false);

        if (!$this->isSpectator()) {
            if ($this->isSurvival()) {
                $this->setFlying(false);
            }
            $this->setSilent(false);
            $this->checkGroundState(0, 0, 0, 0, 0, 0);
        } else {
            $this->setFlying(true);
            $this->setSilent();
            $this->onGround = false;
            $this->sendPosition($this->location, null, null, MovePlayerPacket::MODE_TELEPORT);
        }
    }
}