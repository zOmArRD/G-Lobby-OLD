<?php
declare(strict_types=1);

namespace CortexPE\Commando\args;

use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function count;
use function explode;
use function preg_match;
use function substr;

class Vector3Argument extends BaseArgument
{
    /**
     * @return int
     */
    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_POSITION;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'x y z';
    }

    /**
     * @param string        $testString
     * @param CommandSender $sender
     *
     * @return bool
     */
    public function canParse(string $testString, CommandSender $sender): bool
    {
        $coords = explode(' ', $testString);
        if (count($coords) === 3) {
            foreach ($coords as $coord) {
                if (!$this->isValidCoordinate($coord, $sender instanceof Vector3)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $coordinate
     * @param bool   $locatable
     *
     * @return bool
     */
    public function isValidCoordinate(string $coordinate, bool $locatable): bool
    {
        return (bool)preg_match('/^(?:' . ($locatable ? '(?:~-|~\+)?' : '') . '-?(?:\d+|\d*\.\d+))' . ($locatable ? '|~' : '') . '$/', $coordinate);
    }

    /**
     * @param string        $argument
     * @param CommandSender $sender
     *
     * @return mixed
     */
    public function parse(string $argument, CommandSender $sender)
    {
        $coords = explode(' ', $argument);
        $vals = [];
        foreach ($coords as $k => $coord) {
            $offset = 0;
            // if it's locatable and starts with ~- or ~+
            if ($sender instanceof Vector3 && preg_match('/^(?:~-|~\+)|~/', $coord)) {
                // this will work with -n, +n and "" due to typecast later
                $offset = substr($coord, 1);

                // replace base coordinate with actual entity coordinates
                switch ($k) {
                    case 0:
                        $coord = $sender->x;
                        break;
                    case 1:
                        $coord = $sender->y;
                        break;
                    case 2:
                        $coord = $sender->z;
                        break;
                }
            }
            $vals[] = (float)$coord + (float)$offset;
        }
        return new Vector3(...$vals);
    }

    /**
     * Returns how much command arguments
     * it takes to build the full argument
     *
     * @return int
     */
    public function getSpanLength(): int
    {
        return 3;
    }
}