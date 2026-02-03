<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa;

use App\Support\AdminNavigation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use LaravelPlus\Pwa\Contracts\PushSubscriptionRepositoryInterface;
use LaravelPlus\Pwa\Contracts\PwaServiceInterface;
use LaravelPlus\Pwa\Repositories\PushSubscriptionRepository;
use LaravelPlus\Pwa\Services\ManifestService;
use LaravelPlus\Pwa\Services\PushNotificationService;
use LaravelPlus\Pwa\Services\PwaService;
use LaravelPlus\Pwa\Services\ServiceWorkerService;
use Throwable;

final class PwaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pwa.php', 'pwa');

        $this->app->bind(PushSubscriptionRepositoryInterface::class, PushSubscriptionRepository::class);

        $this->app->singleton(ManifestService::class);
        $this->app->singleton(ServiceWorkerService::class);
        $this->app->singleton(PushNotificationService::class);

        $this->app->singleton(PwaServiceInterface::class, fn ($app) => new PwaService(
            $app->make(PushSubscriptionRepositoryInterface::class),
            $app->make(PushNotificationService::class),
        ));

        $this->app->singleton(PwaService::class, fn ($app) => $app->make(PwaServiceInterface::class));
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerResources();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerBladeDirectives();
        $this->registerAdminNavigation();
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/pwa.php' => config_path('pwa.php'),
            ], 'pwa-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'pwa-migrations');

            $this->publishes([
                __DIR__ . '/../resources/stubs/offline.html' => public_path('offline.html'),
            ], 'pwa-assets');

            $this->publishes([
                __DIR__ . '/../skills/pwa-development' => base_path('.claude/skills/pwa-development'),
            ], 'pwa-skills');

            $this->publishes([
                __DIR__ . '/../skills/pwa-development' => base_path('.github/skills/pwa-development'),
            ], 'pwa-skills-github');
        }
    }

    private function registerResources(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'pwa');
    }

    private function registerRoutes(): void
    {
        if (!$this->isPwaEnabled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->isAdminEnabled()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        }
    }

    /**
     * Check if the PWA package is enabled via DB setting or config fallback.
     */
    private function isPwaEnabled(): bool
    {
        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            try {
                $dbValue = \LaravelPlus\GlobalSettings\Models\Setting::get('package.pwa.enabled');

                if ($dbValue !== null) {
                    return in_array($dbValue, ['1', 'true', true, 1], true);
                }
            } catch (Throwable) {
                // Table may not exist yet during migrations
            }
        }

        return (bool) config('pwa.enabled', true);
    }

    /**
     * Check if admin routes should be enabled via DB setting or config fallback.
     */
    private function isAdminEnabled(): bool
    {
        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            try {
                $dbValue = \LaravelPlus\GlobalSettings\Models\Setting::get('package.pwa.enabled');

                if ($dbValue !== null) {
                    return in_array($dbValue, ['1', 'true', true, 1], true);
                }
            } catch (Throwable) {
                // Table may not exist yet during migrations
            }
        }

        return (bool) config('pwa.admin.enabled', true);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
                Console\Commands\GenerateIconsCommand::class,
                Console\Commands\GenerateServiceWorkerCommand::class,
                Console\Commands\ClearCacheCommand::class,
                Console\Commands\VapidCommand::class,
            ]);
        }
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('pwaHead', fn (): string => "<?php if(config('pwa.enabled', true) && \Illuminate\Support\Facades\Route::has('pwa.manifest')) { echo view('pwa::head')->render(); } ?>");
        Blade::directive('pwaScripts', fn (): string => "<?php if(config('pwa.enabled', true) && \Illuminate\Support\Facades\Route::has('pwa.serviceworker')) { echo view('pwa::scripts')->render(); } ?>");
    }

    private function registerAdminNavigation(): void
    {
        if (!$this->isPwaEnabled()) {
            return;
        }

        $this->callAfterResolving(AdminNavigation::class, function (AdminNavigation $nav): void {
            $prefix = config('pwa.admin.prefix', 'admin/pwa');

            $nav->register('pwa', 'PWA', 'Smartphone', [
                ['title' => 'Settings', 'href' => "/{$prefix}", 'icon' => 'Settings'],
            ], 60);
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            PushSubscriptionRepositoryInterface::class,
            PwaServiceInterface::class,
            PwaService::class,
            ManifestService::class,
            ServiceWorkerService::class,
            PushNotificationService::class,
        ];
    }
}
