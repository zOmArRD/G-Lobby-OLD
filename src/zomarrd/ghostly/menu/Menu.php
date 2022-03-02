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

namespace zomarrd\ghostly\menu;

use pocketmine\utils\EnumTrait;
use zomarrd\ghostly\menu\lobbyselector\LobbySelector;
use zomarrd\ghostly\menu\server\ServerManagerForm;
use zomarrd\ghostly\menu\serverselector\ServerSelector;

/**
 * @method static LobbySelector LOBBY_SELECTOR()
 * @method static ServerManagerForm SERVER_MANAGER_FORM()
 * @method static ServerSelector SERVER_SELECTOR()
 */
class Menu
{
    public const GUI_TYPE = 'gui';
    public const FORM_TYPE = 'form';

    use EnumTrait;

    protected static function setup(): void
    {
        self::_registryRegister('lobby_selector', new LobbySelector());
        self::_registryRegister('server_manager_form', new ServerManagerForm());
        self::_registryRegister('server_selector', new ServerSelector());
    }
}