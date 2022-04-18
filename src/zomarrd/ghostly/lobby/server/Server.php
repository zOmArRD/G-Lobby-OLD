<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 17/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\server;

use zomarrd\ghostly\lobby\database\Database;
use zomarrd\ghostly\lobby\database\mysql\queries\SelectQuery;
use zomarrd\ghostly\lobby\database\mysql\queries\UpdateRowQuery;
use zomarrd\ghostly\lobby\Ghostly;

final class Server
{
    public function __construct(
        private string $name,
        private string $ip,
        private int    $port,
        private bool   $online,
        private int    $maxPlayers,
        private int    $onlinePlayers,
        private bool   $isWhitelisted,
        private string $category
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): void
    {
        if (!$online) {
            Database::getMysql()->runAsync(new UpdateRowQuery(serialize([
                'online' => 0,
                'onlineplayers' => 0
            ]), 'name', $this->name, 'servers'));
            $this->onlinePlayers = 0;
        }

        $this->online = $online;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(int $maxPlayers): void
    {
        $this->maxPlayers = $maxPlayers;
    }

    public function getOnlinePlayers(): int
    {
        return $this->onlinePlayers;
    }

    public function setOnlinePlayers(int $onlinePlayers): void
    {
        $this->onlinePlayers = $onlinePlayers;
    }

    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    public function setIsWhitelisted(bool $isWhitelisted): void
    {
        $this->isWhitelisted = $isWhitelisted;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function sync(): void
    {
        $this->setMaxPlayers(Ghostly::getInstance()->getServer()->getMaxPlayers());
        $this->setOnlinePlayers(count(Ghostly::getInstance()->getServer()->getOnlinePlayers()));
        $this->setIsWhitelisted(Ghostly::getInstance()->getServer()->hasWhitelist());

        Database::getMysql()->runAsync(new UpdateRowQuery(serialize([
            'maxplayers' => $this->maxPlayers,
            'onlineplayers' => $this->onlinePlayers,
            'whitelisted' => $this->isWhitelisted,
        ]), 'name', $this->name, 'servers'));
    }

    public function syncRemote(): void
    {
        Database::getMysql()->runAsync(new SelectQuery("SELECT * FROM servers WHERE name = '$this->name';"), function($rows) {
            $row = $rows[0];
            if ($row !== null) {
                $this->setOnline((bool)$row['online']);
                $this->setMaxPlayers((int)$row['maxplayers']);
                $this->setOnlinePlayers((int)$row['onlineplayers']);
                $this->setIsWhitelisted((bool)$row['whitelisted']);
                return;
            }

            $this->setOnline(false);
        });
    }

    public function getStatus(): string
    {
        if (!$this->isOnline()) {
            return '§r§c' . 'OFFLINE';
        }

        if ($this->isWhitelisted()) {
            return '§r§c' . 'WHITELISTED';
        }

        return sprintf('§r§7Players: §f%s§7/§f%s', $this->getOnlinePlayers(), $this->getMaxPlayers());
    }
}