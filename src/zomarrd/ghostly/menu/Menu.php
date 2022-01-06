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
use zomarrd\ghostly\menu\lobbyselector\LobbySelectorForm;
use zomarrd\ghostly\menu\lobbyselector\LobbySelectorGUI;

/**
 * @method static LobbySelectorGUI LOBBY_SELECTOR_GUI()
 * @method static LobbySelectorForm LOBBY_SELECTOR_FORM()
 */
class Menu
{
	use EnumTrait;

	protected static function setup(): void
	{
		self::_registryRegister('lobby_selector_gui', new LobbySelectorGUI());
		self::_registryRegister('lobby_selector_form', new LobbySelectorForm());
	}
}