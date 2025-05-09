<?php

declare(strict_types=1);

namespace muqsit\simplepackethandler\monitor;

use Closure;
use pocketmine\network\mcpe\NetworkSession;

interface IPacketMonitor
{

    /**
     * @param Closure                                                    $handler
     *
     * @return IPacketMonitor
     *
     * @phpstan-template TServerboundPacket of ServerboundPacket
     * @phpstan-param Closure(TServerboundPacket, NetworkSession) : void $handler
     */
    public function monitorIncoming(Closure $handler): IPacketMonitor;

    /**
     * @param Closure                                                    $handler
     *
     * @return IPacketMonitor
     *
     * @phpstan-template TClientboundPacket of ClientboundPacket
     * @phpstan-param Closure(TClientboundPacket, NetworkSession) : void $handler
     */
    public function monitorOutgoing(Closure $handler): IPacketMonitor;

    /**
     * @param Closure                                                    $handler
     *
     * @return IPacketMonitor
     *
     * @phpstan-template TServerboundPacket of ServerboundPacket
     * @phpstan-param Closure(TServerboundPacket, NetworkSession) : void $handler
     */
    public function unregisterIncomingMonitor(Closure $handler): IPacketMonitor;

    /**
     * @param Closure                                                    $handler
     *
     * @return IPacketMonitor
     *
     * @phpstan-template TClientboundPacket of ClientboundPacket
     * @phpstan-param Closure(TClientboundPacket, NetworkSession) : void $handler
     */
    public function unregisterOutgoingMonitor(Closure $handler): IPacketMonitor;
}