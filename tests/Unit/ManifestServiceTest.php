<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Tests\Unit;

use LaravelPlus\Pwa\Services\ManifestService;
use Tests\TestCase;

final class ManifestServiceTest extends TestCase
{
    private ManifestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ManifestService();
    }

    public function test_generate_returns_array_with_required_fields(): void
    {
        $manifest = $this->service->generate();

        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('short_name', $manifest);
        $this->assertArrayHasKey('start_url', $manifest);
        $this->assertArrayHasKey('display', $manifest);
        $this->assertArrayHasKey('theme_color', $manifest);
        $this->assertArrayHasKey('background_color', $manifest);
    }

    public function test_generate_uses_config_values(): void
    {
        config(['pwa.manifest.name' => 'My Test App']);
        config(['pwa.manifest.theme_color' => '#abcdef']);

        $manifest = $this->service->generate();

        $this->assertSame('My Test App', $manifest['name']);
        $this->assertSame('#abcdef', $manifest['theme_color']);
    }

    public function test_generate_excludes_null_description(): void
    {
        config(['pwa.manifest.description' => null]);

        $manifest = $this->service->generate();

        $this->assertArrayNotHasKey('description', $manifest);
    }

    public function test_generate_includes_description_when_set(): void
    {
        config(['pwa.manifest.description' => 'A great app']);

        $manifest = $this->service->generate();

        $this->assertSame('A great app', $manifest['description']);
    }

    public function test_to_json_returns_valid_json(): void
    {
        $json = $this->service->toJson();

        $decoded = json_decode($json, true);

        $this->assertNotNull($decoded);
        $this->assertIsArray($decoded);
    }

    public function test_generate_excludes_empty_categories(): void
    {
        config(['pwa.manifest.categories' => []]);

        $manifest = $this->service->generate();

        $this->assertArrayNotHasKey('categories', $manifest);
    }

    public function test_generate_includes_categories_when_set(): void
    {
        config(['pwa.manifest.categories' => ['sports', 'fitness']]);

        $manifest = $this->service->generate();

        $this->assertSame(['sports', 'fitness'], $manifest['categories']);
    }
}
