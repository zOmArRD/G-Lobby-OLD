<?php

namespace CortexPE\Commando\store;


use CortexPE\Commando\exception\CommandoException;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\Server;

/**
 *
 */
class SoftEnumStore
{
    /** @var CommandEnum[] */
    private static array $enums = [];

    /**
     * @param string $name
     *
     * @return CommandEnum|null
     */
    public static function getEnumByName(string $name): ?CommandEnum
    {
        return static::$enums[$name] ?? null;
    }

    /**
     * @return CommandEnum[]
     */
    public static function getEnums(): array
    {
        return static::$enums;
    }

    /**
     * @param CommandEnum $enum
     *
     * @return void
     */
    public static function addEnum(CommandEnum $enum): void
    {
        static::$enums[$enum->getName()] = $enum;
        self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_ADD);
    }

    /**
     * @throws CommandoException
     */
    public static function updateEnum(string $enumName, array $values): void
    {
        if (self::getEnumByName($enumName) === null) {
            throw new CommandoException('Unknown enum named ' . $enumName);
        }
        $enum = new CommandEnum($enumName, $values);
        self::$enums[$enum->getName()] = $enum;
        self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_SET);
    }

    /**
     * @throws CommandoException
     */
    public static function removeEnum(string $enumName): void
    {
        if (($enum = self::getEnumByName($enumName)) === null) {
            throw new CommandoException('Unknown enum named ' . $enumName);
        }
        unset(static::$enums[$enumName]);
        self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_REMOVE);
    }

    /**
     * @param CommandEnum $enum
     * @param int         $type
     *
     * @return void
     */
    public static function broadcastSoftEnum(CommandEnum $enum, int $type): void
    {
        $pk = new UpdateSoftEnumPacket();
        $pk->enumName = $enum->getName();
        $pk->values = $enum->getValues();
        $pk->type = $type;
        self::broadcastPacket($pk);
    }

    /**
     * @param ClientboundPacket $pk
     *
     * @return void
     */
    private static function broadcastPacket(ClientboundPacket $pk): void
    {
        ($sv = Server::getInstance())->broadcastPackets($sv->getOnlinePlayers(), [$pk]);
    }
}