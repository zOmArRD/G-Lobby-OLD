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
use pocketmine\event\Listener;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use zomarrd\ghostly\config\ConfigManager;
use zomarrd\ghostly\events\PlayerEvents;
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\queries\RegisterServerQuery;
use zomarrd\ghostly\mysql\queries\UpdateRowQuery;
use zomarrd\ghostly\player\language\LangHandler;
use zomarrd\ghostly\player\skin\MojangAdapter;
use zomarrd\ghostly\server\ServerManager;

final class Ghostly extends PluginBase
{
	public static Ghostly $instance;
	public static AttachableLogger $logger;

	protected function onLoad(): void
	{
		self::$instance = $this;
		self::$logger = $this->getLogger();
		new ConfigManager();
		SkinAdapterSingleton::set(new MojangAdapter());
		MySQL::createTables();
	}

	/**
	 * @throws HookAlreadyRegistered
	 */
	protected function onEnable(): void
	{
		$prefix = PREFIX;
		new ServerManager();
		new LangHandler();

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$this->registerEvents([
			new PlayerEvents()
		]);

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

	protected function onDisable(): void
	{
		ServerManager::getInstance()?->getCurrentServer()?->setOnline(false);
	}

	public static function getInstance(): Ghostly
	{
		return self::$instance;
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

	/**
	 * I currently have no idea. Apparently it returns the plugin directory, not plugin_data
	 * @return string
	 */
	public function getResourcesFolder(): string
	{
		return $this->getFile() . 'resources/';
	}
}