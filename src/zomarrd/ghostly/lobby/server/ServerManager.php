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

namespace zomarrd\ghostly\lobby\server;

use pocketmine\scheduler\ClosureTask;
use zomarrd\ghostly\database\mysql\MySQL;
use zomarrd\ghostly\lobby\database\mysql\queries\RegisterServerQuery;
use zomarrd\ghostly\lobby\database\mysql\queries\SelectQuery;
use zomarrd\ghostly\lobby\Ghostly;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;

final class ServerManager
{
    private static ServerManager $instance;
    private ?Server $current_server = null;

    /** @var Server[] */
    private array $servers = [];

    public function __construct()
    {
        self::$instance = $this;
        $this->init();
    }

    public function init(): void
    {
        Ghostly::$logger->info(PREFIX . 'Registering the server in the database');
        new RegisterServerQuery(Server['name']);
        //sleep(1); //WHY YES ?
        $this->reloadServers();

        Ghostly::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->getCurrentServer()?->sync();
            foreach ($this->getServers() as $server) {
                $server->syncRemote();
            }
        }), 60);
    }

    public function getCurrentServerName(): string
    {
        return Server['name'];
    }

    public function reloadServers(GhostlyPlayer $player = null): void
    {
        $this->servers = [];
        $cServerName = $this->getCurrentServerName();

        MySQL::getInstance()->runAsync(new SelectQuery('SELECT * FROM servers'), function($rows) use ($cServerName, $player) {
            foreach ($rows as $row) {
                $server = new Server($row['name'], $row['ip'], (int)$row['port'], (bool)$row['online'], (int)$row['maxplayers'], (int)$row['onlineplayers'], (bool)$row['whitelisted'], $row['category']);
                if ($row['name'] === $cServerName) {
                    $this->current_server = $server;
                } else {
                    $this->servers[] = $server;
                }

                Ghostly::$logger->info(sprintf('%sThe server (%s) has been registered in the database_backup!', PREFIX, $server->getName()));
                $player?->sendMessage(sprintf('%sThe server (%s) has been registered in the database_backup!', PREFIX, $server->getName()));
            }
        });
    }

    public function getCurrentServer(): ?Server
    {
        return $this->current_server;
    }

    /**
     * @return Server[]
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    public static function getInstance(): ServerManager
    {
        return self::$instance;
    }

    /**
     * It takes care of looking for the server.
     *
     * @param string $name
     *
     * @return Server|null the object {@link Server}.
     */
    public function getServerByName(string $name): ?Server
    {
        if ($name === Server['name']) {
            return $this->getCurrentServer();
        }

        foreach ($this->getServers() as $server) {
            if ($server->getName() !== $name) {
                continue;
            }

            return $server;
        }

        return null;
    }

    /**
     * @return int The total players of the Network.
     */
    public function getNetworkPlayers(): int
    {
        $players = 0;

        foreach ($this->getServers() as $server) {
            $players += $server->getOnlinePlayers();
        }

        $players += count(Ghostly::getInstance()->getServer()->getOnlinePlayers());

        return $players;
    }

    /**
     * @return int The total number of players on the network.
     */
    public function getNetworkMaxPlayers(): int
    {
        $maxPlayers = Ghostly::getInstance()->getServer()->getMaxPlayers();

        foreach ($this->getServers() as $server) {
            if (!$server->isOnline()) {
                continue;
            }

            $maxPlayers += $server->getMaxPlayers();
        }

        return $maxPlayers;
    }
}