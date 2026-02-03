<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServiceWorkerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_worker_returns_javascript_content_type(): void
    {
        $response = $this->get('/sw.js');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/javascript');
        $response->assertHeader('Service-Worker-Allowed', '/');
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }

    public function test_service_worker_contains_cache_version(): void
    {
        config(['pwa.service_worker.cache_version' => 'v42']);

        $response = $this->get('/sw.js');

        $response->assertOk();
        $this->assertStringContainsString('v42', $response->getContent());
    }

    public function test_service_worker_contains_offline_page(): void
    {
        config(['pwa.service_worker.offline_page' => '/offline']);

        $response = $this->get('/sw.js');

        $response->assertOk();
        $this->assertStringContainsString('/offline', $response->getContent());
    }

    public function test_service_worker_contains_cache_strategies(): void
    {
        $response = $this->get('/sw.js');

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('cache-first', $content);
        $this->assertStringContainsString('network-first', $content);
    }
}
