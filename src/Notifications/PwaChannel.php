<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Notifications;

use Illuminate\Notifications\Notification;
use LaravelPlus\Pwa\Contracts\PwaServiceInterface;

final class PwaChannel
{
    public function __construct(
        private readonly PwaServiceInterface $pwaService,
    ) {}

    /**
     * Send the given notification via PWA push.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toPwa')) {
            return;
        }

        /** @var array{title: string, body: string, icon?: string, url?: string} $payload */
        $payload = $notification->toPwa($notifiable);

        $this->pwaService->sendToUser($notifiable->getKey(), $payload);
    }
}
