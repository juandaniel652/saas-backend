<?php

declare(strict_types=1);

namespace App\Core\Events;

use App\Core\Container\Container;

final class EventDispatcher
{
    /** @var array<class-string, class-string[]> */
    private array $listeners = [];

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @param class-string $eventClass
     * @param class-string $listenerClass
     */
    public function listen(string $eventClass, string $listenerClass): void
    {
        $this->listeners[$eventClass][] = $listenerClass;
    }

    public function dispatch(EventInterface $event): void
    {
        $listeners = $this->listeners[$event::class] ?? [];

        foreach ($listeners as $listenerClass) {
            /** @var ListenerInterface $listener */
            $listener = $this->container->make($listenerClass);
            $listener->handle($event);
        }
    }
}