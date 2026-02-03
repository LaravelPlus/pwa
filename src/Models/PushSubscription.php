<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PushSubscription extends Model
{
    use HasFactory;

    protected $table = 'pwa_push_subscriptions';

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_token',
        'content_encoding',
        'user_agent',
        'last_used_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<\Illuminate\Foundation\Auth\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \LaravelPlus\Pwa\Database\Factories\PushSubscriptionFactory
    {
        return \LaravelPlus\Pwa\Database\Factories\PushSubscriptionFactory::new();
    }
}
