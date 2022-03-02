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

namespace zomarrd\ghostly\player;

use JetBrains\PhpStorm\Pure;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\UIProfile;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use zomarrd\ghostly\extensions\scoreboard\Scoreboard;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\item\ItemManager;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\player\language\Language;
use zomarrd\ghostly\player\permission\PermissionKey;
use zomarrd\ghostly\server\Server;
use zomarrd\ghostly\server\ServerManager;
use zomarrd\ghostly\world\Lobby;

/**
 * @todo check<PARTICLE_EYE_DESPAWN>
 */
class GhostlyPlayer extends Player
{
    public int|float $cooldown = 0;
    private string $currentLang = Language::ENGLISH_US;
    private bool $loaded = false, $scoreboard = true;
    private Scoreboard $scoreboard_session;
    private ItemManager $itemManager;
    private bool $canInteractItem = true;

    public function canInteractItem(): bool
    {
        return $this->canInteractItem;
    }

    public function isScoreboard(): bool
    {
        return $this->scoreboard;
    }

    public function setScoreboard(bool $scoreboard): void
    {
        $this->scoreboard = $scoreboard;
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
            return $this->getLangHandler()->getLanguage($this->locale);
        }

        return $this->getLangHandler()->getLanguage($this->currentLang);
    }

    #[Pure] public function getLangHandler(): LangHandler
    {
        return LangHandler::getInstance();
    }

    public function onUpdate(int $currentTick): bool
    {
        if (!$this->isLoaded()) {
            $this->scoreboard_session = new Scoreboard($this);
            $this->itemManager = new ItemManager($this);
            $this->setLoaded();
        }

        if ($currentTick % 10 === 0) {
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

    public function getScoreboardSession(): Scoreboard
    {
        return $this->scoreboard_session;
    }

    public function setLanguage(string $lang): void
    {
        $this->currentLang = $lang;
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

    public function getLobbyItems(): void
    {
        $this->getInventory()?->clearAll();
        foreach (['item-servers' => 0, 'item-cosmetics' => 4, 'item-lobby' => 7, 'item-config' => 8] as $item => $index) {
            $this->setItem($index, $this->getItemManager()->get($item));
        }
    }

    private function setItem(int $index, Item $item): void
    {
        $this->getInventory()?->setItem($index, $item);
    }

    public function getItemManager(): ItemManager
    {
        return $this->itemManager;
    }

    public function teleport_to_lobby(): void
    {
        $lobby = Lobby::getInstance();

        if ($lobby !== null) {
            $this->teleport($lobby->getSpawnPosition(), $lobby->getSpawnYaw(), $lobby->getSpawnPitch());
        }
    }

    public function isOp(): bool
    {
        return Ghostly::getInstance()->getServer()->isOp($this->getName());
    }

    public function closeInventory(): void
    {
        $this->sendSound(LevelSoundEvent::DROP_SLOT);
        $session = InvMenuHandler::getPlayerManager()->get($this);
        $session->removeCurrentMenu();
    }

    public function sendSound(int $sound, string $type = "level-sound"): void
    {
        switch ($type) {
            case "level-event":
                $this->getNetworkSession()->sendDataPacket(LevelEventPacket::create($sound, 0, $this->getLocation()->asVector3()));
                break;
            case "level-sound":
                $this->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::create($sound, $this->getLocation()->asVector3(), -1, ":", false, false));
                break;
        }
    }

    public function server_transfer_task(string|Server $server): void
    {
        Ghostly::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($server): void {
            $this->transferTo($server);
        }), 25);
    }

    /**
     * @param string|Server $server
     *
     * @return void
     * @todo Add Queue System for this shit omg!
     */
    public function transferTo(string|Server $server): void
    {
        if (is_string($server)) {
            $server = ServerManager::getInstance()->getServerByName($server);
        }

        if (is_null($server)) {
            $this->sendSound(LevelSoundEvent::EXPLODE);
            $this->sendTranslated(LangKey::SERVER_CONNECT_ERROR_3);
            $this->setCanInteractItem();
            return;
        }

        if ($server->getName() === Ghostly::SERVER) {
            $this->sendSound(LevelSoundEvent::RANDOM_ANVIL_USE);
            $this->sendTranslated(LangKey::SERVER_CONNECT_ERROR_1);
            $this->setCanInteractItem();
            return;
        }

        if (!$server->isOnline()) {
            $this->sendSound(LevelSoundEvent::RANDOM_ANVIL_USE);
            $this->sendTranslated(LangKey::SERVER_NOT_ONLINE);
            $this->setCanInteractItem();
            return;
        }

        if ($server->isWhitelist() && !$this->hasPermission(PermissionKey::GHOSTLY_SERVER_CONNECT_WHITELISTED)) {
            $this->sendSound(LevelSoundEvent::RANDOM_ANVIL_USE);
            $this->sendTranslated(LangKey::SERVER_IS_WHITELISTED);
            $this->setCanInteractItem();
            return;
        }

        if (!$this->hasPermission(PermissionKey::GHOSTLY_SERVER_JOIN_BYPASS) && $server->getPlayers() >= $server->getMaxPlayers()) {
            $this->sendSound(LevelEvent::SOUND_IGNITE, 'level-event');
            $this->sendTranslated(LangKey::SERVER_CONNECT_ERROR_4);
            $this->setCanInteractItem();
            return;
        }

        $this->sendTranslated(LangKey::SERVER_CONNECTING, ["{SERVER-NAME}" => $server->getName()]);
        $this->transfer($server->getName(), 0, "Transfer to {$server->getName()}");
    }

    public function sendTranslated(string $string, array $replaceable = []): void
    {
        $this->sendMessage($this->getTranslation($string, $replaceable));
    }

    public function getTranslation(string $string, array $replaceable = []): string
    {
        return $this->getLang()->getMessage($string, $replaceable);
    }

    public function setCanInteractItem(bool $canInteractItem = true): void
    {
        $this->canInteractItem = $canInteractItem;
    }

    public function hasCooldown(float|int $time): bool
    {
        return time() - $this->cooldown < $time;
    }

    public function setCooldown(): void
    {
        $this->cooldown = time();
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