<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Services;

use Illuminate\Support\Facades\DB;
use LaravelPlus\Pwa\Contracts\PushSubscriptionRepositoryInterface;
use LaravelPlus\Pwa\Contracts\PwaServiceInterface;

final class PwaService implements PwaServiceInterface
{
    public function __construct(
        private readonly PushSubscriptionRepositoryInterface $repository,
        private readonly PushNotificationService $pushService,
    ) {}

    /**
     * @param  array{endpoint: string, keys: array{p256dh: string, auth: string}, content_encoding?: string}  $data
     */
    public function subscribe(int $userId, array $data): void
    {
        DB::transaction(function () use ($userId, $data): void {
            $existing = $this->repository->findByEndpoint($data['endpoint']);

            if ($existing) {
                return;
            }

            $this->repository->create([
                'user_id' => $userId,
                'endpoint' => $data['endpoint'],
                'p256dh_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['content_encoding'] ?? 'aesgcm',
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    public function unsubscribe(string $endpoint): void
    {
        $this->repository->deleteByEndpoint($endpoint);
    }

    /**
     * @param  array{title: string, body: string, icon?: string, url?: string}  $payload
     */
    public function sendToUser(int $userId, array $payload): void
    {
        $subscriptions = $this->repository->forUser($userId);

        if ($subscriptions->isEmpty()) {
            return;
        }

        $this->pushService->send($subscriptions, $payload);
    }

    /**
     * @param  array{title: string, body: string, icon?: string, url?: string}  $payload
     */
    public function sendToAll(array $payload): void
    {
        $subscriptions = $this->repository->all();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $this->pushService->send($subscriptions, $payload);
    }
}
