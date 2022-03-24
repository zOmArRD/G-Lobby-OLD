<?php
declare(strict_types=1);

namespace CortexPE\Commando\args;


use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use function preg_match;

/**
 *
 */
class BlockPositionArgument extends Vector3Argument
{
    /**
     * @param string $coordinate
     * @param bool   $locatable
     *
     * @return bool
     */
    public function isValidCoordinate(string $coordinate, bool $locatable): bool
    {
        return (bool)preg_match('/^(?:' . ($locatable ? '(?:~-|~\+)?' : '') . '-?\d+)' . ($locatable ? '|~' : '') . '$/', $coordinate);
    }

    /**
     * @param string        $argument
     * @param CommandSender $sender
     *
     * @return Vector3
     */
    public function parse(string $argument, CommandSender $sender): Vector3
    {
        return parent::parse($argument, $sender)->floor();
    }
}