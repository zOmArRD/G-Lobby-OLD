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

namespace zomarrd\ghostly\menu\server;

use jojoe77777\FormAPI\SimpleForm;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\server\ServerManager;

final class ServerManagerForm
{
	public function build(GhostlyPlayer $player): void
	{
		$form = new SimpleForm(function (GhostlyPlayer $player, $data): void {
			if (isset($data)) {
				switch ($data) {
					case "reload_servers":
						ServerManager::getInstance()->reloadServers($player);
						$player->sendMessage(PREFIX . "Servers have been reloaded from the database!");
						break;
					default:
						return;
				}
			}
		});

		$form->setTitle("Server Manager");
		$form->addButton("Reload Servers\n§7From Database", $form::IMAGE_TYPE_NULL, "", "reload_servers");
		$form->addButton($player->getTranslation(LangKey::FORM_BUTTON_CLOSE), $form::IMAGE_TYPE_NULL, '', 'close');
		$player->sendForm($form);
	}
}