<?php
declare(strict_types=1);
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 22/5/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */

namespace zomarrd\ghostly\lobby\player\settings;

// Class to store the settings of the player and the data.
use zomarrd\ghostly\lobby\player\GhostlyPlayer;

class Settings
{
    private array $geoLocation = [];

    public function __construct(private GhostlyPlayer $player) {}

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }

    public function getGeoLocation(): array
    {
        return [
            'ip' => $this->geoLocation['ip'] ? 'Unknown' : $this->geoLocation['ip'],
            'city' => $this->geoLocation['city'] ? 'Unknown' : $this->geoLocation['city'],
            'region' => $this->geoLocation['region'] ? 'Unknown' : $this->geoLocation['region'],
            'country' => $this->geoLocation['country'] ? 'Unknown' : $this->geoLocation['country'],
            'continent' => $this->geoLocation['continent'] ? 'Unknown' : $this->geoLocation['continent'],
        ];
    }



}