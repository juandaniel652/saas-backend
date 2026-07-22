<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Channels;

use App\Core\Mail\Mailer;
use App\Modules\Notifications\Contracts\NotificationChannelInterface;

final class EmailChannel implements NotificationChannelInterface
{
    public function __construct(private readonly Mailer $mailer)
    {
    }

    public function send(string $to, string $subject, string $message): void
    {
        $this->mailer->send($to, $subject, $message);
    }
}