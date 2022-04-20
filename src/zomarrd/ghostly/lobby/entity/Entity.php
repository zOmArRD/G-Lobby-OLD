<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 7/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\entity;

use pocketmine\utils\EnumTrait;

/**
 * @method static EntityManager ENTITY()
 */
final class Entity
{
    public const EXTRA = '{JOIN}';
    public const DISCORD = 'discord';
    public const STORE = 'store';
    public const OMAR = 'zomarrd';
    public const COMBO = 'Combo';
    public const PRACTICE = 'Practice';
    public const UHC = 'UHC';
    public const UHC_RUN = 'UHC Run';
    public const HCF = 'HCF';
    public const KITMAP = 'KITMAP';

    use EnumTrait;

    /** @noinspection MethodCanBePrivateInspection */
    protected static function setup(): void
    {
        self::_registryRegister('entity', new EntityManager());
    }
}