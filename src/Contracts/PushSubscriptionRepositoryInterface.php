<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Pwa\Models\PushSubscription;

interface PushSubscriptionRepositoryInterface
{
    /**
     * Find a subscription by its endpoint.
     */
    public function findByEndpoint(string $endpoint): ?PushSubscription;

    /**
     * Get all subscriptions for a user.
     *
     * @return Collection<int, PushSubscription>
     */
    public function forUser(int $userId): Collection;

    /**
     * Get all subscriptions.
     *
     * @return Collection<int, PushSubscription>
     */
    public function all(): Collection;

    /**
     * Create a new subscription.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PushSubscription;

    /**
     * Delete a subscription by its endpoint.
     */
    public function deleteByEndpoint(string $endpoint): bool;

    /**
     * Get the total number of subscriptions.
     */
    public function count(): int;
}
