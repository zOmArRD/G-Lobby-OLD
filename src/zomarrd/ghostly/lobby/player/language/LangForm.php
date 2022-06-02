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

namespace zomarrd\ghostly\lobby\player\language;

use GhostlyMC\DatabaseAPI\mysql\MySQL;
use GhostlyMC\FormAPI\SimpleForm;
use zomarrd\ghostly\lobby\database\mysql\queries\UpdateRowQuery;
use zomarrd\ghostly\lobby\player\GhostlyPlayer;

final class LangForm
{
    public function __construct(private GhostlyPlayer $player)
    {
        $this->showForm();
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(static function(Ghostlyplayer $player, $data) {
            if (isset($data)) {
                if ($data === 'close') {
                    return;
                }

                if ($data === 'predetermined') {
                    $player->setLanguage($player->getLocale());
                    $player->sendTranslated(LangKey::LANGUAGE_APPLIED, ['LANGUAGE}' => $player->getLang()->getLocale()]);
                    return;
                }

                $lang = explode('-', $data);
                if ($player->getLang()->getLocale() === $lang[0]) {
                    $player->sendTranslated(LangKey::LANGUAGE_APPLY_FAILED);
                    return;
                }

                MySQL::runAsync(new UpdateRowQuery(['lang' => $lang[0]], 'player', $player->getName(), 'ghostly_playerdata'),
                    static function()  use ($player, $lang): void {
                    $player->setLanguage($lang[0]);
                    $player->getLobbyItems();
                    $player->sendTranslated(LangKey::LANGUAGE_APPLIED, ['{LANGUAGE}' => $lang[1]]);
                });

            }
        });

        $form->setTitle($player->getTranslation(LangKey::LANGUAGE_SELECT));
        $form->setContent($player->getTranslation(LangKey::LANGUAGE_SELECT_DESC));

        foreach (getLanguages() as $lang) {
            $form->addButton('§9' . $lang->getName(), $form::IMAGE_TYPE_NULL, '', $lang->getLocale() . "-{$lang->getName()}");
        }

        $form->addButton($player->getTranslation(LangKey::FORM_DEFAULT) . "\n §7(§a{$player->getLocale()}§7)", $form::IMAGE_TYPE_NULL, '', 'predetermined');
        $form->addButton($player->getTranslation(LangKey::FORM_CLOSE), $form::IMAGE_TYPE_NULL, '', 'close');

        $player->sendForm($form);
    }

    public function getPlayer(): GhostlyPlayer
    {
        return $this->player;
    }
}