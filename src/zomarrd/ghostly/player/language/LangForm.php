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
use zomarrd\ghostly\mysql\MySQL;
use zomarrd\ghostly\mysql\queries\UpdateRowQuery;
use zomarrd\ghostly\player\GhostlyPlayer;

final class LangForm
{
    public function __construct(private GhostlyPlayer $player)
    {
        $this->showForm();
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(static function (Ghostlyplayer $player, $data) {
            if (isset($data)) {
                if ($data === 'close') {
                    return;
                }

                if ($data === 'predetermined') {
                    $player->setLanguage($player->getLocale());
                    $player->sendTranslated(LangKey::LANG_APPLIED_CORRECTLY, ["{NEW-LANG}" => $player->getLang()->getLocale()]);
                    return;
                }

                $lang = explode('-', $data);
                if ($player->getLang()->getLocale() === $lang[0]) {
                    $player->sendTranslated(LangKey::LANG_APPLIED_FAIL);
                    return;
                }

                $player->setLanguage($lang[0]);
                $player->sendTranslated(LangKey::LANG_APPLIED_CORRECTLY, ["{NEW-LANG}" => $lang[1]]);
                MySQL::runAsync(new UpdateRowQuery(serialize(["lang" => $lang[0]]), "player", $player->getName(), "player_config"));
                $player->getLobbyItems();
            }
        });
        $form->setTitle($player->getTranslation(LangKey::SET_LANGUAGE));
        $form->setContent($player->getTranslation(LangKey::AVAILABLE_LANGUAGE));

        foreach ($player->getLangHandler()->getLanguages() as $lang) {
            $form->addButton('§9' . $lang->getName(), $form::IMAGE_TYPE_NULL, '', $lang->getLocale() . "-{$lang->getName()}");
        }

        $form->addButton($player->getTranslation(LangKey::FORM_BUTTON_PREDETERMINED) . "\n §7(§a{$player->getLocale()}§7)", $form::IMAGE_TYPE_NULL, '', 'predetermined');
        $form->addButton($player->getTranslation(LangKey::FORM_BUTTON_CLOSE), $form::IMAGE_TYPE_NULL, '', 'close');

        $player->sendForm($form);
    }

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }
}