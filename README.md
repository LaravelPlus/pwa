# LaravelPlus PWA

Progressive Web App support for Laravel 12+. Serves a web app manifest and a generated service worker, ships an offline page, and adds Web Push notifications (VAPID), background sync, icon generation and an admin settings screen.

## Requirements

- PHP 8.4+
- Laravel 12 or 13
- `ext-gd` (icon generation)
- `minishlink/web-push` ^9.0 — only if you use push notifications

## Installation

The package is not on Packagist yet. Add the repository, then require it:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/LaravelPlus/pwa" }
]
```

```bash
composer require laravelplus/pwa:dev-master
```

Or keep it as a git submodule and a composer `path` repository (`packages/laravelplus/pwa`).

Run the installer — it publishes the config and the offline page:

```bash
php artisan pwa:install              # config + assets
php artisan pwa:install --migrations # also publish migrations (push subscriptions)
php artisan migrate
```

## Setup

Add the directives to your root layout:

```blade
<head>
    @pwaHead      {{-- manifest link, theme-color, icons --}}
</head>
<body>
    ...
    @pwaScripts   {{-- registers /sw.js --}}
</body>
```

Both render nothing when `pwa.enabled` is `false`.

Generate icons from a square source PNG (default `public/icon.png`, written to `public/icons`):

```bash
php artisan pwa:generate-icons --source=public/icon.png
```

## Routes

| Method | URI | Purpose |
|---|---|---|
| GET | `/manifest.webmanifest` | Web app manifest built from `pwa.manifest` |
| GET | `/sw.js` | Service worker |
| GET | `/offline` | Offline fallback page |
| POST | `/api/pwa/push/subscribe` | Store a push subscription (auth) |
| POST | `/api/pwa/push/unsubscribe` | Remove a push subscription (auth) |
| POST | `/api/pwa/sync` | Background-sync retry endpoint (auth) |
| GET/PATCH | `/admin/pwa` | Admin settings (configurable prefix + middleware) |

## Configuration

`config/pwa.php` (publish with `--tag=pwa-config`):

- **`manifest`** — name, colours, display mode, scope, icons, shortcuts.
- **`service_worker`** — `cache_version`, Vite asset precaching, `offline_page`, per-path `cache_strategies` (`CacheFirst`, `NetworkFirst`, …) and `exclude_patterns` (Horizon, Telescope and `/admin/*` by default).
- **`push`** — enable flag and VAPID keys (`VAPID_SUBJECT`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`).
- **`background_sync`** — enable flag and retry endpoint.
- **`icons`** — source image, output directory, sizes.
- **`admin`** — enable flag, route prefix (`admin/pwa`) and middleware.

Set `pwa.enabled` to `false` to disable routes and directives entirely.

## Push notifications

```bash
composer require minishlink/web-push
php artisan pwa:vapid   # generates VAPID keys and writes them to .env
```

Send through the `PwaChannel` from any notification by adding `toPwa()`:

```php
use LaravelPlus\Pwa\Notifications\PwaChannel;

public function via(object $notifiable): array
{
    return [PwaChannel::class];
}

public function toPwa(object $notifiable): array
{
    return [
        'title' => 'Order shipped',
        'body'  => 'Your order is on its way.',
        'url'   => '/orders/42',                  // optional
        'icon'  => '/icons/icon-192x192.png',     // optional
    ];
}
```

## Commands

| Command | Description |
|---|---|
| `pwa:install` | Publish config and assets (`--migrations`, `--skills`) |
| `pwa:generate-sw` | Write the service worker to `public/sw.js` |
| `pwa:generate-icons` | Generate the icon set from a source PNG (`--source`, `--output`) |
| `pwa:vapid` | Generate VAPID keys and write them to `.env` |
| `pwa:clear-cache` | Bump the cache version so clients drop old caches |

## Publishing

| Tag | Contents |
|---|---|
| `pwa-config` | `config/pwa.php` |
| `pwa-migrations` | Push subscription migration |
| `pwa-assets` | `public/offline.html` |
| `pwa-skills` / `pwa-skills-github` | Claude Code skill for working with the package |

## Testing

The tests run inside a host Laravel app:

```bash
vendor/bin/pest packages/laravelplus/pwa/tests
```

## License

MIT
