<?php

declare(strict_types=1);

namespace muqsit\invmenu\session;

use Closure;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\session\network\PlayerNetwork;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

final class PlayerSession
{

    private ?InvMenuInfo $current = null;

    public function __construct(
        private Player        $player,
        private PlayerNetwork $network
    ) {}

    /**
     * @internal
     */
    public function finalize(): void
    {
        if ($this->current !== null) {
            $this->current->graphic->remove($this->player);
            $this->player->removeCurrentWindow();
        }
        $this->network->dropPending();
    }

    public function getCurrent(): ?InvMenuInfo
    {
        return $this->current;
    }

    /**
     * @param InvMenuInfo|null $current
     * @param    (Closure(bool) : bool)|null $callback
     *
     * @internal use InvMenu::send() instead.
     *
     */
    public function setCurrentMenu(?InvMenuInfo $current, ?Closure $callback = null): void
    {
        $this->current = $current;

        if ($this->current !== null) {
            $this->network->waitUntil($this->current->graphic->getAnimationDuration(), function(bool $success) use ($callback): bool {
                if ($this->current !== null) {
                    if ($success && $this->current->graphic->sendInventory($this->player, $this->current->menu->getInventory())) {
                        if ($callback !== null) {
                            $callback(true);
                        }
                        return false;
                    }

                    $this->removeCurrentMenu();
                    if ($callback !== null) {
                        $callback(false);
                    }
                }
                return false;
            });
        } else {
            $this->network->wait($callback ?? static fn(bool $success): bool => false);
        }
    }

    /**
     * @return bool
     * @internal use Player::removeCurrentWindow() instead
     */
    public function removeCurrentMenu(): bool
    {
        if ($this->current !== null) {
            $server = $this->player->getServer();
            $uuid = $this->player->getUniqueId();
            $graphic = $this->current->graphic;
            InvMenuHandler::getRegistrant()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function() use ($server, $uuid, $graphic): void {
                $player = $server->getPlayerByUUID($uuid);
                if ($player !== null) {
                    $graphic->remove($player);
                }
            }), 1);
            $this->setCurrentMenu(null);
            return true;
        }
        return false;
    }

    public function getNetwork(): PlayerNetwork
    {
        return $this->network;
    }
}
