<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Factories;

use App\Core\Container\Container;
use App\Modules\Notifications\Channels\EmailChannel;
use App\Modules\Notifications\Contracts\NotificationChannelInterface;
use InvalidArgumentException;

final class NotificationChannelFactory
{
    public function __construct(private readonly Container $container)
    {
    }

    public function make(string $channel): NotificationChannelInterface
    {
        return match ($channel) {
            'email' => $this->container->make(EmailChannel::class),
            default => throw new InvalidArgumentException("Canal de notificacion desconocido: {$channel}"),
        };
    }
}