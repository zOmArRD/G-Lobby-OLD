<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 29/12/2021
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\extensions\scoreboard;

use zomarrd\ghostly\lobby\config\ConfigManager;

class Scoreboard extends ScoreAPI
{
    /** This is to replace blanks */
    private const EMPTY_CACHE = [
        "§0\e",
        "§1\e",
        "§2\e",
        "§3\e",
        "§4\e",
        "§5\e",
        "§6\e",
        "§7\e",
        "§8\e",
        "§9\e",
        "§a\e",
        "§b\e",
        "§c\e",
        "§d\e",
        "§e\e"
    ];

    private int $count = 0;

    final public function setScoreboard(): void
    {
        if (!$this->getPlayer()->isScoreboard()) {
            if ($this->isObjectiveName()) {
                $this->remove();
            }
            return;
        }

        if ($this->count > 14) {
            $this->count = 0;
        }

        $this->new('ghostly.lobby', $this->getConfig()['display'][$this->count]);
        $this->updateScoreboard();
        $this->count++;
    }

    private function getConfig(): array
    {
        return ConfigManager::getServerConfig()?->get('scoreboard');
    }

    private function updateScoreboard(): void
    {
        if (!$this->getPlayer()->isQueue()) {
            foreach ($this->getConfig()['lines-normal'] as $line => $string) {
                $msg = $this->replaceData($line, (string)$string);
                $this->setLine($line, $msg);
            }
        } else {
            foreach ($this->getConfig()['lines-queue'] as $line => $string) {
                $msg = $this->replaceData($line, (string)$string);
                $this->setLine($line, $msg);
            }
        }
    }

    final public function replaceData(int $line, string $string): string
    {
        return empty($string) ? self::EMPTY_CACHE[$line] ?? '' : checkStrings($string, $this->getPlayer());
    }
}