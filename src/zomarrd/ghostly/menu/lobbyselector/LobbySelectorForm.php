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
use zomarrd\ghostly\player\GhostlyPlayer;
use zomarrd\ghostly\player\language\LangKey;
use zomarrd\ghostly\server\LocalServer;
use zomarrd\ghostly\server\ServerManager;

final class LobbySelectorForm
{
	private SimpleForm $form;

	public function addServerButton(array $server_data, SimpleForm $form): void
	{
		$currentServer = ServerManager::getInstance()->getCurrentServer();
		$server_name = $server_data["server_name"];
		$server = ServerManager::getInstance()->getServerByName($server_name);
		$text = "§r";

		if ($currentServer !== null && $server_name === $currentServer->getServerName()) {
			$text .= "§a{$server_name}\n§7Players: §f{$currentServer->getPlayers()}§7/§f{$currentServer->getMaxPlayers()}";
		}

		$text = $server === null || !$server->isOnline() ? $text . "§c{$server_name}\n§cOFFLINE" : $text . "§a{$server_name}\n§7Players: §f{$server->getPlayers()}§7/§f{$server->getMaxPlayers()}";

		$form->addButton($text, $server_data["form_image_type"], $server_data["form_image_path"], $server_name);
	}

	public function getForm(): SimpleForm
	{
		return $this->form;
	}

	public function build(GhostlyPlayer $player): void
	{
		$this->form = new SimpleForm(function (GhostlyPlayer $player, $data): void {
			if (isset($data)) {
				if ($data === "close") {
					return;
				}

				$player->transfer_to_lobby($data);
			}
		});

		$this->form->setTitle("Lobby Selector");
		$this->form->setContent($player->getTranslation(LangKey::LOBBY_SERVER_FORM_CONTENT));
		$servers = LocalServer::getInstance()->getServers();

		foreach ($servers as $server) {
			if ($server["category"] !== "Lobby") {
				continue;
			}

			$this->addServerButton($server, $this->getForm());
		}

		$this->getForm()->addButton($player->getTranslation(LangKey::FORM_BUTTON_CLOSE), $this->getForm()::IMAGE_TYPE_NULL, '', 'close');
		$player->sendForm($this->getForm());
	}
}