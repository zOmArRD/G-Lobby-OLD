<?php
declare(strict_types=1);

namespace CortexPE\Commando\args;


use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function preg_match;

class IntegerArgument extends BaseArgument
{
    /**
     * @return int
     */
    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_INT;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'int';
    }

    /**
     * @param string        $testString
     * @param CommandSender $sender
     *
     * @return bool
     */
    public function canParse(string $testString, CommandSender $sender): bool
    {
        return (bool)preg_match('/^-?(?:\d+)$/', $testString);
    }

    /**
     * @param string        $argument
     * @param CommandSender $sender
     *
     * @return int
     */
    public function parse(string $argument, CommandSender $sender): int
    {
        return (int)$argument;
    }
}