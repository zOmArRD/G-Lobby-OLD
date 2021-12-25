<?php
declare(strict_types=1);

namespace CortexPE\Commando\args;


use pocketmine\command\CommandSender;

class BooleanArgument extends StringEnumArgument
{
    protected const VALUES = [
        'true' => true,
        'false' => false,
    ];

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'bool';
    }

    /**
     * @param string        $argument
     * @param CommandSender $sender
     *
     * @return mixed
     */
    public function parse(string $argument, CommandSender $sender)
    {
        return $this->getValue($argument);
    }
}