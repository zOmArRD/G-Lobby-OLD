<?php
declare(strict_types=1);

namespace CortexPE\Commando\args;


use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class TextArgument extends RawStringArgument
{
    /**
     * @return int
     */
    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'text';
    }

    /**
     * Returns how much command arguments
     * it takes to build the full argument
     *
     * @return int
     */
    public function getSpanLength(): int
    {
        return PHP_INT_MAX;
    }

    /**
     * @param string        $testString
     * @param CommandSender $sender
     *
     * @return bool
     */
    public function canParse(string $testString, CommandSender $sender): bool
    {
        return $testString !== '';
    }
}