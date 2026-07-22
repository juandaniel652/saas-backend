<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Contracts;

interface NotificationChannelInterface
{
    public function send(string $to, string $subject, string $message): void;
}