<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Repositories;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Pwa\Contracts\PushSubscriptionRepositoryInterface;
use LaravelPlus\Pwa\Models\PushSubscription;

final class PushSubscriptionRepository implements PushSubscriptionRepositoryInterface
{
    public private(set) string $modelClass = PushSubscription::class;

    public function findByEndpoint(string $endpoint): ?PushSubscription
    {
        return $this->modelClass::query()
            ->where('endpoint', $endpoint)
            ->first();
    }

    /**
     * @return Collection<int, PushSubscription>
     */
    public function forUser(int $userId): Collection
    {
        return $this->modelClass::query()
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * @return Collection<int, PushSubscription>
     */
    public function all(): Collection
    {
        return $this->modelClass::query()->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PushSubscription
    {
        return $this->modelClass::query()->create($data);
    }

    public function deleteByEndpoint(string $endpoint): bool
    {
        return (bool) $this->modelClass::query()
            ->where('endpoint', $endpoint)
            ->delete();
    }

    public function count(): int
    {
        return $this->modelClass::query()->count();
    }
}
