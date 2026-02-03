<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Contracts;

interface PwaServiceInterface
{
    /**
     * Subscribe a user to push notifications.
     *
     * @param  array{endpoint: string, keys: array{p256dh: string, auth: string}, content_encoding?: string}  $data
     */
    public function subscribe(int $userId, array $data): void;

    /**
     * Unsubscribe a push notification endpoint.
     */
    public function unsubscribe(string $endpoint): void;

    /**
     * Send a push notification to a specific user.
     *
     * @param  array{title: string, body: string, icon?: string, url?: string}  $payload
     */
    public function sendToUser(int $userId, array $payload): void;

    /**
     * Send a push notification to all subscribers.
     *
     * @param  array{title: string, body: string, icon?: string, url?: string}  $payload
     */
    public function sendToAll(array $payload): void;
}
