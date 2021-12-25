<?php

declare(strict_types=1);

namespace muqsit\simplepackethandler\monitor;

use Closure;

interface IPacketMonitor{

    /**
     * @param Closure $handler
     *
     * @return IPacketMonitor
     */
	public function monitorIncoming(Closure $handler) : IPacketMonitor;

    /**
     * @param Closure $handler
     *
     * @return IPacketMonitor
     */
	public function monitorOutgoing(Closure $handler) : IPacketMonitor;
}