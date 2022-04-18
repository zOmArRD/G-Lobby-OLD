<?php
/** @noinspection MethodVisibilityInspection */
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 18/2/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby;

use AttachableLogger;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\player\PlayerInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use ReflectionException;
use zomarrd\ghostly\lobby\commands\entity\EntityCommand;
use zomarrd\ghostly\lobby\commands\language\LangCommand;
use zomarrd\ghostly\lobby\commands\mute\GlobalMuteCommand;
use zomarrd\ghostly\lobby\commands\server\ServerCommand;
use zomarrd\ghostly\lobby\config\ConfigManager;
use zomarrd\ghostly\lobby\database\Database;
use zomarrd\ghostly\lobby\entity\Entity;
use zomarrd\ghostly\lobby\events\HumanListener;
use zomarrd\ghostly\lobby\events\ItemInteractListener;
use zomarrd\ghostly\lobby\events\PlayerListener;
use zomarrd\ghostly\lobby\exception\ExtensionMissing;
use zomarrd\ghostly\lobby\menu\Menu;
use zomarrd\ghostly\lobby\network\login\LoginPacketHandler;
use zomarrd\ghostly\lobby\network\skin\MojangAdapter;
use zomarrd\ghostly\lobby\player\language\LangHandler;
use zomarrd\ghostly\lobby\security\ChatHandler;
use zomarrd\ghostly\lobby\server\queue\QueueManager;
use zomarrd\ghostly\lobby\server\ServerManager;
use zomarrd\ghostly\lobby\task\GlobalTask;

final class Ghostly extends PluginBase
{
    public static Ghostly $instance;
    public static AttachableLogger $logger;
    public static array $colors;
    public static bool $is_proxy_server = true;
    public static Config $server_items;
    public static QueueManager $queueManager;
    private static bool $globalMute = false;

    public static function getInstance(): Ghostly
    {
        return self::$instance;
    }

    public static function isGlobalMute(): bool
    {
        return self::$globalMute;
    }

    public static function setGlobalMute(bool $globalMute): void
    {
        self::$globalMute = $globalMute;
    }

    /**
     * @return string the plugin directory, not plugin_data
     */
    public function getResourcesFolder(): string
    {
        return $this->getFile() . 'resources/';
    }

    protected function onLoad(): void
    {
        self::$instance = $this;
        self::$logger = $this->getLogger();
        //self::$colors = json_decode(file_get_contents($this->getFile() . "resources/colors.json"), true, 512, JSON_THROW_ON_ERROR);
        self::$server_items = new Config($this->getFile() . "resources/servers_items.yml");

        if (!extension_loaded('mysqli')) {
            throw new ExtensionMissing("mysqli");
        }

        self::$queueManager = new QueueManager();
        new ConfigManager();

        /** It is responsible for registering all providers for connections to databases */
        new Database();

        Database::getMysql()->createTables();
    }

    /**
     * @throws HookAlreadyRegistered
     * @throws ReflectionException
     * @noinspection PhpUndefinedMethodInspection
     * @noinspection PhpUndefinedFieldInspection
     */
    protected function onEnable(): void
    {
        $prefix = PREFIX;

        new ServerManager();
        new LangHandler();
        new ChatHandler();

        SkinAdapterSingleton::set(new MojangAdapter());

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        Menu::SERVER_SELECTOR()->register();
        Menu::LOBBY_SELECTOR()->register();

        $this->registerEvents([new PlayerListener(), new ItemInteractListener(), new HumanListener()]);

        $this->registerCommands("bukkit", [
            new ServerCommand($this),
            new LangCommand($this, "lang"),
            new GlobalMuteCommand($this, 'globalmute'),
            new EntityCommand($this, 'entity'),
        ]);

        $this->getServer()->getPluginManager()->registerEvent(QueryRegenerateEvent::class, function(QueryRegenerateEvent $event): void {
            $info = $event->getQueryInfo();
            $server_manager = ServerManager::getInstance();
            $info->setPlugins([$this]);
            $info->setPlayerCount($server_manager->getNetworkPlayers());
            $info->setMaxPlayerCount($server_manager->getNetworkMaxPlayers());
        }, EventPriority::LOWEST, $this);

        Entity::ENTITY()->register();

        self::getQueueManager()->enable($this);

        foreach ($this->getServer()->getNetwork()->getInterfaces() as $interface) {
            if (!$interface instanceof RakLibInterface) {
                continue;
            }

            $interface->setPacketLimit(PHP_INT_MAX);
        }

        /**
         * If the server is proxy, it will establish a custom login method.
         */
        if (self::$is_proxy_server) {
            $this->getServer()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event): void {
                $packet = $event->getPacket();
                if (!$packet instanceof LoginPacket) {
                    return;
                }

                $event->getOrigin()->setHandler(new LoginPacketHandler($this->getServer(), $event->getOrigin(), function(PlayerInfo $info) use ($event): void {
                    (function() use ($info): void {
                        $this->info = $info;
                        $this->getLogger()->info("Player: " . TextFormat::RED . $info->getUsername() . TextFormat::RESET);
                        $this->getLogger()->setPrefix($this->getLogPrefix());
                    })->call($event->getOrigin());
                }, function(bool $isAuthenticated, bool $authRequired, ?string $error, ?string $clientPubKey) use ($event): void {
                    (function() use ($authRequired, $error, $clientPubKey): void {
                        $this->setAuthenticationStatus(true, $authRequired, $error, $clientPubKey);
                    })->call($event->getOrigin());
                }));
            }, EventPriority::LOWEST, $this, true);
        }

        $this->getScheduler()->scheduleRepeatingTask(new GlobalTask(), 1);

        $this->getServer()->getNetwork()->setName("§l§cGhostlyMC §f» §r§aOPEN!!");
        self::$logger->notice('§c' . <<<INFO


         $$$$$$\  $$\                             $$\     $$\           $$\      $$\  $$$$$$\  
        $$  __$$\ $$ |                            $$ |    $$ |          $$$\    $$$ |$$  __$$\ 
        $$ /  \__|$$$$$$$\   $$$$$$\   $$$$$$$\ $$$$$$\   $$ |$$\   $$\ $$$$\  $$$$ |$$ /  \__|
        $$ |$$$$\ $$  __$$\ $$  __$$\ $$  _____|\_$$  _|  $$ |$$ |  $$ |$$\$$\$$ $$ |$$ |      
        $$ |\_$$ |$$ |  $$ |$$ /  $$ |\$$$$$$\    $$ |    $$ |$$ |  $$ |$$ \$$$  $$ |$$ |      
        $$ |  $$ |$$ |  $$ |$$ |  $$ | \____$$\   $$ |$$\ $$ |$$ |  $$ |$$ |\$  /$$ |$$ |  $$\ 
        \$$$$$$  |$$ |  $$ |\$$$$$$  |$$$$$$$  |  \$$$$  |$$ |\$$$$$$$ |$$ | \_/ $$ |\$$$$$$  |
         \______/ \__|  \__| \______/ \_______/    \____/ \__| \____$$ |\__|     \__| \______/ 
                                                              $$\   $$ |                       
                                                              \$$$$$$  |                       
                                                               \______/         
                                                               
         $prefix §fCreated by zOmArRD :)                                                                     
INFO
        );

    }

    public function registerEvents(array $listeners): void
    {
        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    public function registerCommands(string $fallbackPrefix, array $commands): void
    {
        $this->getServer()->getCommandMap()->registerAll($fallbackPrefix, $commands);
    }

    public static function getQueueManager(): QueueManager
    {
        return self::$queueManager;
    }

    protected function onDisable(): void
    {
        ServerManager::getInstance()->getCurrentServer()?->setOnline(false);
    }
}