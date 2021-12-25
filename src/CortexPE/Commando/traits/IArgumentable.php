<?php

declare(strict_types=1);

namespace CortexPE\Commando\traits;


use CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;

/**
 *
 */
interface IArgumentable
{
    /**
     * @return string
     */
    public function generateUsageMessage(): string;

    /**
     * @return bool
     */
    public function hasArguments(): bool;

    /**
     * @return BaseArgument[][]
     */
    public function getArgumentList(): array;

    /**
     * @param array         $rawArgs
     * @param CommandSender $sender
     *
     * @return array
     */
    public function parseArguments(array $rawArgs, CommandSender $sender): array;

    /**
     * @param int          $position
     * @param BaseArgument $argument
     *
     * @return void
     */
    public function registerArgument(int $position, BaseArgument $argument): void;
}