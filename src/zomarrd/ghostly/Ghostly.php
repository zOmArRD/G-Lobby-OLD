<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 24/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly;

use AttachableLogger;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use JsonException;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\player\PlayerInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use zomarrd\ghostly\commands\entity\EntityCommand;
use zomarrd\ghostly\commands\language\LangCommand;
use zomarrd\ghostly\commands\mute\GlobalMuteCommand;
use zomarrd\ghostly\commands\server\ServerCommand;
use zomarrd\ghostly\config\ConfigManager;
use zomarrd\ghostly\entity\Entity;
use zomarrd\ghostly\events\HumanListener;
use zomarrd\ghostly\events\ItemInteractListener;
use zomarrd\ghostly\events\PlayerListener;
use zomarrd\ghostly\exception\ExtensionMissing;
use zomarrd\ghostly\menu\Menu;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\network\login\LoginPacketHandler;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\server\ServerManager;
use zomarrd\ghostly\task\GlobalTask;

final class Ghostly extends PluginBase
{
	public const SERVER = "Lobby-1";
	public const CATEGORY = "Lobby";

	public static Ghostly $instance;
	public static AttachableLogger $logger;
	public static array $colors;
	public static bool $is_proxy_server = true;
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
	 * I currently have no idea. Apparently it returns the plugin directory, not plugin_data
	 *
	 * @return string
	 */
	public function getResourcesFolder(): string
	{
		return $this->getFile() . 'resources/';
	}

	/**
	 * @throws JsonException
	 */
	protected function onLoad(): void
	{
		self::$instance = $this;
		self::$logger = $this->getLogger();
		self::$colors = json_decode(file_get_contents($this->getFile() . "resources/colors.json"), true, 512, JSON_THROW_ON_ERROR);

		if (!extension_loaded('mysqli')) {
			throw new ExtensionMissing("mysqli");
		}

		new ConfigManager();
		MySQL::createTables();
	}

	/**
	 * @throws HookAlreadyRegistered
	 * @throws \ReflectionException
	 */
	protected function onEnable(): void
	{
		$prefix = PREFIX;

		new ServerManager();
		new LangHandler();

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		if (!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}

		Menu::SERVER_SELECTOR_GUI()->register();

		$this->registerEvents([
			new PlayerListener(),
			new ItemInteractListener(),
			new HumanListener()
		]);

		$this->registerCommands("bukkit", [
			new ServerCommand($this),
			new LangCommand($this, "lang"),
			new GlobalMuteCommand($this, 'globalmute'),
			new EntityCommand($this, 'entity')
		]);

		$this->getServer()->getPluginManager()->registerEvent(QueryRegenerateEvent::class, function (QueryRegenerateEvent $event): void {
			$info = $event->getQueryInfo();
			$server_manager = ServerManager::getInstance();
			$info->setPlugins([$this]);
			$info->setPlayerCount($server_manager->getNetworkPlayers());
			$info->setMaxPlayerCount($server_manager->getNetworkMaxPlayers());
		}, EventPriority::LOWEST, $this);

		Entity::ENTITY()->register();

		foreach ($this->getServer()->getNetwork()->getInterfaces() as $interface) {
			if (!$interface instanceof RakLibInterface) {
				continue;
			}

			$interface->setPacketLimit(PHP_INT_MAX);
		}

		if (self::$is_proxy_server) {
			$this->getServer()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function (DataPacketReceiveEvent $event): void {
				$packet = $event->getPacket();
				if (!$packet instanceof LoginPacket) {
					return;
				}

				$event->getOrigin()->setHandler(new LoginPacketHandler($this->getServer(), $event->getOrigin(),
					function (PlayerInfo $info) use ($event): void {
						(function () use ($info): void {

							/** @noinspection PhpUndefinedFieldInspection */
							$this->info = $info;

							/** @noinspection PhpUndefinedMethodInspection */
							$this->getLogger()->info("Player: " . TextFormat::RED . $info->getUsername() . TextFormat::RESET);

							/** @noinspection PhpUndefinedMethodInspection */
							$this->getLogger()->setPrefix($this->getLogPrefix());
						})->call($event->getOrigin());
					}, function (bool $isAuthenticated, bool $authRequired, ?string $error, ?string $clientPubKey) use ($event): void {
						(function () use ($isAuthenticated, $authRequired, $error, $clientPubKey): void {
							/** @noinspection PhpUndefinedMethodInspection */
							$this->setAuthenticationStatus(true, $authRequired, $error, $clientPubKey);
						})->call($event->getOrigin());
					}));

			}, EventPriority::LOWEST, $this, true);
		}

		$this->getScheduler()->scheduleRepeatingTask(new GlobalTask(), 1);

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

	/**
	 * @param Listener[] $listeners
	 *
	 * @return void
	 */
	public function registerEvents(array $listeners): void
	{
		$manager = $this->getServer()->getPluginManager();
		foreach ($listeners as $listener) {
			$manager->registerEvents($listener, $this);
		}
	}

	public function registerCommands(
		string $fallbackPrefix,
		array  $commands
	): void
	{
		$this->getServer()->getCommandMap()->registerAll($fallbackPrefix, $commands);
	}

	protected function onDisable(): void
	{
		ServerManager::getInstance()->getCurrentServer()?->setOnline(false);
	}
}