<?php
declare(strict_types=1);

namespace CortexPE\Commando\constraint;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ConsoleRequiredConstraint extends BaseConstraint {

    /**
     * @param CommandSender $sender
     * @param string        $aliasUsed
     * @param array         $args
     *
     * @return bool
     */
    public function test(CommandSender $sender, string $aliasUsed, array $args): bool {
        return $this->isVisibleTo($sender);
    }

    /**
     * @param CommandSender $sender
     *
     * @return bool
     */
    public function isVisibleTo(CommandSender $sender): bool {
		return !($sender instanceof Player);
	}

    /**
     * @param CommandSender $sender
     * @param string        $aliasUsed
     * @param array         $args
     *
     * @return void
     */
    public function onFailure(CommandSender $sender, string $aliasUsed, array $args): void {
        $sender->sendMessage(TextFormat::RED . 'This command must be executed from a server console.'); // f*ck off grammar police
    }
}