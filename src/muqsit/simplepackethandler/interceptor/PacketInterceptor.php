<?php

declare(strict_types=1);

namespace muqsit\simplepackethandler\interceptor;

use Closure;
use pocketmine\plugin\Plugin;
use ReflectionException;

final class PacketInterceptor implements IPacketInterceptor
{

    private PacketInterceptorListener $listener;

    public function __construct(Plugin $register, int $priority, bool $handleCancelled)
    {
        $this->listener = new PacketInterceptorListener($register, $priority, $handleCancelled);
    }

    /**
     * @throws ReflectionException
     */
    public function interceptIncoming(Closure $handler): IPacketInterceptor
    {
        $this->listener->interceptIncoming($handler);
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function interceptOutgoing(Closure $handler): IPacketInterceptor
    {
        $this->listener->interceptOutgoing($handler);
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function unregisterIncomingInterceptor(Closure $handler): IPacketInterceptor
    {
        $this->listener->unregisterIncomingInterceptor($handler);
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function unregisterOutgoingInterceptor(Closure $handler): IPacketInterceptor
    {
        $this->listener->unregisterOutgoingInterceptor($handler);
        return $this;
    }
}