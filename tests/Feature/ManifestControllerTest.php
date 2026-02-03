<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ManifestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_returns_json_with_correct_content_type(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json');
    }

    public function test_manifest_contains_required_fields(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertJsonStructure([
            'name',
            'short_name',
            'start_url',
            'display',
            'theme_color',
            'background_color',
        ]);
    }

    public function test_manifest_name_matches_config(): void
    {
        config(['pwa.manifest.name' => 'Test PWA App']);
        config(['pwa.manifest.short_name' => 'TestPWA']);

        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Test PWA App',
            'short_name' => 'TestPWA',
        ]);
    }

    public function test_manifest_excludes_null_optional_fields(): void
    {
        config(['pwa.manifest.description' => null]);
        config(['pwa.manifest.categories' => []]);

        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $json = $response->json();

        $this->assertArrayNotHasKey('description', $json);
        $this->assertArrayNotHasKey('categories', $json);
    }
}
