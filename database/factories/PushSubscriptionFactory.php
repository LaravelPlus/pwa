<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPlus\Pwa\Models\PushSubscription;

/**
 * @extends Factory<PushSubscription>
 */
final class PushSubscriptionFactory extends Factory
{
    protected $model = PushSubscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/' . fake()->uuid(),
            'p256dh_key' => base64_encode(fake()->sha256()),
            'auth_token' => base64_encode(fake()->sha1()),
            'content_encoding' => 'aesgcm',
            'user_agent' => fake()->userAgent(),
            'last_used_at' => null,
        ];
    }
}
