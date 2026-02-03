# PWA Development Skill

## Overview
This skill activates when working with the `laravelplus/pwa` package — Progressive Web App support for Laravel.

## Package Structure
- **Config**: `config/pwa.php` — manifest, service worker, push, background sync, icons, admin settings
- **Models**: `PushSubscription` — stores push notification subscriptions per user
- **Services**: `ManifestService`, `ServiceWorkerService`, `PwaService`, `PushNotificationService`
- **Contracts**: `PwaServiceInterface`, `PushSubscriptionRepositoryInterface`
- **Enums**: `CacheStrategy` — CacheFirst, NetworkFirst, StaleWhileRevalidate, CacheOnly, NetworkOnly
- **Controllers**: ManifestController, ServiceWorkerController, PushSubscriptionController, BackgroundSyncController, Admin\PwaController
- **Commands**: `pwa:install`, `pwa:generate-icons`, `pwa:clear-cache`, `pwa:vapid`

## Key Routes
- `GET /manifest.webmanifest` — Web app manifest (JSON)
- `GET /sw.js` — Service worker JavaScript
- `GET /offline` — Offline fallback page
- `POST /api/pwa/push/subscribe` — Subscribe to push notifications (auth required)
- `POST /api/pwa/push/unsubscribe` — Unsubscribe from push (auth required)
- `POST /api/pwa/sync` — Background sync replay endpoint (auth required)
- `GET /admin/pwa` — Admin PWA settings
- `PATCH /admin/pwa` — Update admin PWA settings

## Blade Directives
- `@pwaHead` — Add to `<head>` for manifest link, theme-color meta, apple-mobile-web-app meta
- `@pwaScripts` — Add before `</body>` for service worker registration

## Push Notification Channel
Use `PwaChannel` in Laravel notifications:
```php
public function via($notifiable): array
{
    return [PwaChannel::class];
}

public function toPwa($notifiable): array
{
    return ['title' => 'Hello', 'body' => 'World', 'url' => '/'];
}
```

## Background Sync (TypeScript)
```typescript
import { pwaSync } from '@/../../packages/laravelplus/pwa/resources/js/pwa-sync';
await pwaSync('/api/endpoint', { method: 'POST', body: JSON.stringify(data) });
```
