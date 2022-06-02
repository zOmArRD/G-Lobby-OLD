<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 14/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\menu\server;

use GhostlyMC\FormAPI\CustomForm;
use GhostlyMC\FormAPI\SimpleForm;
use zomarrd\ghostly\lobby\config\ConfigManager;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;
use zomarrd\ghostly\lobby\player\language\LangKey;
use zomarrd\ghostly\lobby\server\ServerManager;

final class ServerManagerForm
{
    public function build(GhostlyPlayer $player): void
    {
        $form = new SimpleForm(function(GhostlyPlayer $player, $data): void {
            if (!isset($data)) {
                return;
            }

            switch ($data) {
                case 'reload_servers':
                    ServerManager::getInstance()->reloadServers($player);
                    $player->sendMessage(PREFIX . 'Servers have been reloaded from the database_backup!');
                    break;
                case 'proxy_detect':
                    $form = new CustomForm(function(GhostlyPlayer $player, $data): void {
                        $value = $data[0];
                        $default_value = ConfigManager::getServerConfig()->get('proxy_detect');
                        if ($default_value === $value) {
                            $player->sendMessage(sprintf('%s§cThis option seems to be already activated!', PREFIX));
                            return;
                        }

                        $player->sendMessage(sprintf('%sProxy Detector is enabled: §a %s', PREFIX, $value ? 'true' : 'false'));
                        ConfigManager::getServerConfig()->set('proxy_detect', $value);
                        ConfigManager::getServerConfig()->save();
                    });

                    $form->setTitle('Proxy Detector');
                    $form->addToggle('is enabled?', ConfigManager::getServerConfig()->get('proxy_detect'));
                    $player->sendForm($form);
                    break;
            }
        });

        $form->setTitle('Server Manager');
        $form->addButton("Reload Servers\n§7From Database", $form::IMAGE_TYPE_NULL, '', 'reload_servers');
        $form->addButton("Proxy Detect\n§7Disable/Enable", $form::IMAGE_TYPE_NULL, '', 'proxy_detect');
        $form->addButton($player->getTranslation(LangKey::FORM_CLOSE), $form::IMAGE_TYPE_NULL, '', 'close');
        $player->sendForm($form);
    }
}