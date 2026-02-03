<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Pwa\Models\PushSubscription;
use Tests\TestCase;

final class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_subscribe_to_push(): void
    {
        $response = $this->postJson('/api/pwa/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
            'keys' => [
                'p256dh' => 'test-p256dh-key',
                'auth' => 'test-auth-key',
            ],
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_subscribe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/pwa/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-123',
            'keys' => [
                'p256dh' => 'test-p256dh-key',
                'auth' => 'test-auth-key',
            ],
        ]);

        $response->assertCreated();
        $response->assertJson(['message' => 'Subscribed successfully.']);

        $this->assertDatabaseHas('pwa_push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-123',
        ]);
    }

    public function test_subscribe_requires_valid_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/pwa/push/subscribe', [
            'endpoint' => 'not-a-url',
            'keys' => [
                'p256dh' => 'test-key',
                'auth' => 'test-auth',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('endpoint');
    }

    public function test_subscribe_requires_keys(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/pwa/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('keys');
    }

    public function test_authenticated_user_can_unsubscribe(): void
    {
        $user = User::factory()->create();

        PushSubscription::factory()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/to-remove',
        ]);

        $response = $this->actingAs($user)->postJson('/api/pwa/push/unsubscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/to-remove',
        ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('pwa_push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/to-remove',
        ]);
    }

    public function test_duplicate_subscription_is_ignored(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/duplicate-test';

        PushSubscription::factory()->create([
            'user_id' => $user->id,
            'endpoint' => $endpoint,
        ]);

        $response = $this->actingAs($user)->postJson('/api/pwa/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'new-key',
                'auth' => 'new-auth',
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseCount('pwa_push_subscriptions', 1);
    }
}
