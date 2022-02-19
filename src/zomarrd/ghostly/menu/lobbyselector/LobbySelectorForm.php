<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 4/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\menu\lobbyselector;

use jojoe77777\FormAPI\SimpleForm;
use zomarrd\ghostly\Ghostly;
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\server\Server;
use zomarrd\ghostly\server\ServerManager;

final class LobbySelectorForm
{
	public function build(GhostlyPlayer $player): void
	{
		$form = new SimpleForm(function (GhostlyPlayer $player, $data): void {
			if (isset($data)) {
				if ($data === "close") {
					return;
				}

				$player->transferTo($data);
			}
		});

		$form->setTitle("Lobby Selector");
		$form->setContent($player->getTranslation(LangKey::LOBBY_SERVER_FORM_CONTENT));
		$servers = ServerManager::getInstance()->getServers();
		$current = ServerManager::getInstance()->getCurrentServer();

		if (isset($current)) {
			$this->addServerButton($current, $form);
		}

		foreach ($servers as $server) {
			if ($server->getCategory() !== "Lobby") {
				continue;
			}

			$this->addServerButton($server, $form);
		}

		$form->addButton($player->getTranslation(LangKey::FORM_BUTTON_CLOSE), $form::IMAGE_TYPE_NULL, '', 'close');
		$player->sendForm($form);
	}

	public function addServerButton(Server $server, SimpleForm $form): void
	{
		$text = "§r";

		if ($server->getName() === Ghostly::SERVER) {
			$text .= "§a{$server->getName()} §7[§f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}§7]\n§cYou are already connected here!";
		}

		if ($server->isOnline()) {
			$text .= "§a{$server->getName()} §7[§f{$server->getPlayers()}§f/§7{$server->getMaxPlayers()}§7]\n§eClick to transfer!";
		} else {
			$text .= "§a{$server->getName()} §7[§f{$server->getPlayers()}§f/§7{$server->getMaxPlayers()}§7]\n§cOFFLINE";
		}

		if ($server->isWhitelist()) {
			$text .= "§a{$server->getName()} §7[§f{$server->getPlayers()}§f/§7{$server->getMaxPlayers()}§7]\n§cWHITELISTED";
		}

		$form->addButton($text, $form::IMAGE_TYPE_NULL, "", $server->getName());
	}
}