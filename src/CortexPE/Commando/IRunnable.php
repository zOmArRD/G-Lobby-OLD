<?php
declare(strict_types=1);

namespace CortexPE\Commando;


use CortexPE\Commando\constraint\BaseConstraint;

/**
 * Interface IRunnable
 *
 * An interface which is declares the minimum required information
 * to get background information for a command and/or a sub-command
 *
 * @package CortexPE\Commando
 */
interface IRunnable
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string[]
     */
    public function getAliases(): array;

    /**
     * @return string
     */
    public function getUsageMessage(): string;

    /**
     * @return mixed
     */
    public function getPermission(): mixed; // f*ck. PM didn't declare a return type... reeee

    /**
     * @return BaseConstraint[]
     */
    public function getConstraints(): array;
}