<?php
declare(strict_types=1);

namespace CortexPE\Commando\constraint;


use CortexPE\Commando\IRunnable;
use pocketmine\command\CommandSender;

/**
 *
 */
abstract class BaseConstraint
{
    /** @var IRunnable */
    protected IRunnable $context;

    /**
     * BaseConstraint constructor.
     *
     * "Context" is required so that this new-constraint-system doesn't hinder getting command info
     *
     * @param IRunnable $context
     */
    public function __construct(IRunnable $context)
    {
        $this->context = $context;
    }

    /**
     * @return IRunnable
     */
    public function getContext(): IRunnable
    {
        return $this->context;
    }

    /**
     * @param CommandSender $sender
     * @param string        $aliasUsed
     * @param array         $args
     *
     * @return bool
     */
    abstract public function test(CommandSender $sender, string $aliasUsed, array $args): bool;

    /**
     * @param CommandSender $sender
     * @param string        $aliasUsed
     * @param array         $args
     *
     * @return void
     */
    abstract public function onFailure(CommandSender $sender, string $aliasUsed, array $args): void;

    /**
     * @param CommandSender $sender
     *
     * @return bool
     */
    abstract public function isVisibleTo(CommandSender $sender): bool;
}