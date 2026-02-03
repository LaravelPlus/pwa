<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Tests\Unit;

use LaravelPlus\Pwa\Services\ServiceWorkerService;
use Tests\TestCase;

final class ServiceWorkerServiceTest extends TestCase
{
    private ServiceWorkerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceWorkerService();
    }

    public function test_render_returns_javascript_string(): void
    {
        $js = $this->service->render();

        $this->assertIsString($js);
        $this->assertStringContainsString('CACHE_VERSION', $js);
        $this->assertStringContainsString('addEventListener', $js);
    }

    public function test_render_contains_configured_cache_version(): void
    {
        config(['pwa.service_worker.cache_version' => 'v99']);

        $js = $this->service->render();

        $this->assertStringContainsString('v99', $js);
    }

    public function test_render_contains_offline_page(): void
    {
        config(['pwa.service_worker.offline_page' => '/custom-offline']);

        $js = $this->service->render();

        $this->assertStringContainsString('/custom-offline', $js);
    }

    public function test_get_cache_version_returns_config_value(): void
    {
        config(['pwa.service_worker.cache_version' => 'v5']);

        // Ensure no override file exists
        $overridePath = storage_path('pwa-cache-version.txt');
        if (file_exists($overridePath)) {
            unlink($overridePath);
        }

        $this->assertSame('v5', $this->service->getCacheVersion());
    }

    public function test_get_cache_version_prefers_override_file(): void
    {
        config(['pwa.service_worker.cache_version' => 'v1']);

        $overridePath = storage_path('pwa-cache-version.txt');
        file_put_contents($overridePath, 'v-override-123');

        $this->assertSame('v-override-123', $this->service->getCacheVersion());

        unlink($overridePath);
    }

    public function test_get_precache_urls_returns_empty_when_disabled(): void
    {
        config(['pwa.service_worker.precache_vite_assets' => false]);

        $urls = $this->service->getPrecacheUrls();

        $this->assertSame([], $urls);
    }

    public function test_render_contains_background_sync_flag(): void
    {
        config(['pwa.background_sync.enabled' => true]);

        $js = $this->service->render();

        $this->assertStringContainsString('true', $js);
    }

    public function test_render_contains_exclude_patterns(): void
    {
        config(['pwa.service_worker.exclude_patterns' => ['/admin/*', '/telescope/*']]);

        $js = $this->service->render();

        $this->assertStringContainsString('/admin/*', $js);
        $this->assertStringContainsString('/telescope/*', $js);
    }
}
