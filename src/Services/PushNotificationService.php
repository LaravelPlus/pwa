<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Services;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Pwa\Models\PushSubscription;
use RuntimeException;

final class PushNotificationService
{
    /**
     * Send a push notification to a collection of subscriptions.
     *
     * @param  Collection<int, PushSubscription>  $subscriptions
     * @param  array{title: string, body: string, icon?: string, url?: string}  $payload
     */
    public function send(Collection $subscriptions, array $payload): void
    {
        if (!class_exists(\Minishlink\WebPush\WebPush::class)) {
            throw new RuntimeException(
                'The minishlink/web-push package is required for push notifications. '
                . 'Install it with: composer require minishlink/web-push'
            );
        }

        $auth = [
            'VAPID' => [
                'subject' => config('pwa.push.vapid.subject'),
                'publicKey' => config('pwa.push.vapid.public_key'),
                'privateKey' => config('pwa.push.vapid.private_key'),
            ],
        ];

        /** @var \Minishlink\WebPush\WebPush $webPush */
        $webPush = new \Minishlink\WebPush\WebPush($auth);
        $jsonPayload = json_encode($payload);

        foreach ($subscriptions as $subscription) {
            /** @var \Minishlink\WebPush\Subscription $pushSubscription */
            $pushSubscription = \Minishlink\WebPush\Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->p256dh_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding,
            ]);

            $webPush->queueNotification($pushSubscription, $jsonPayload);
        }

        $expiredEndpoints = [];

        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $expiredEndpoints[] = $report->getRequest()->getUri()->__toString();
            }
        }

        if ($expiredEndpoints !== []) {
            PushSubscription::query()->whereIn('endpoint', $expiredEndpoints)->delete();
        }
    }
}
