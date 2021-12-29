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

namespace zomarrd\ghostly\player\language;

use jojoe77777\FormAPI\SimpleForm;
use zomarrd\ghostly\player\GhostlyPlayer;

final class LangForm
{
	public function __construct(
		private GhostlyPlayer $player
	){$this->showForm();}

	public function showForm(): void
	{
		$player = $this->getPlayer();
		$form = new SimpleForm(static function(Ghostlyplayer $player, $data) {
			if (isset($data)) {
				if ($data === 'close') {
					return;
				}
				$lang = explode('-', $data);
				if ($player->getLang()->getLocale() === $lang[0]) {
					$player->sendTranslated('lang.message.set-fail');
					return;
				}
				$player->setLanguage($lang[0]);
				$player->sendTranslated('lang.message.set', ["{NEW-LANG}" => $lang[1]]);
			}
		});
		$form->setTitle($player->getTranslation('lang.form.title'));
		$form->setContent($player->getTranslation('lang.form.content'));
		foreach($player->getLangHandler()->getLanguages() as $lang) {
			$form->addButton('§9' . $lang->getName(), $form::IMAGE_TYPE_NULL, '', $lang->getLocale() . "-{$lang->getName()}");
		}
		$form->addButton($player->getTranslation('global.form.button.close'), $form::IMAGE_TYPE_NULL, '', 'close');
		$player->sendForm($form);
	}

	public function getPlayer(): GhostlyPlayer
	{
		return $this->player;
	}
}