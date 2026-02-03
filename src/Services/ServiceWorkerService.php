<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Services;

use LaravelPlus\Pwa\Enums\CacheStrategy;

final class ServiceWorkerService
{
    /**
     * Render the service worker JavaScript from the stub template.
     */
    public function render(): string
    {
        $stub = file_get_contents(__DIR__ . '/../../resources/stubs/sw.js.stub');

        $replacements = [
            '__CACHE_VERSION__' => $this->getCacheVersion(),
            '__PRECACHE_URLS__' => json_encode($this->getPrecacheUrls(), JSON_UNESCAPED_SLASHES),
            '__OFFLINE_PAGE__' => config('pwa.service_worker.offline_page', '/offline'),
            '__CACHE_STRATEGIES__' => json_encode($this->getCacheStrategies(), JSON_UNESCAPED_SLASHES),
            '__EXCLUDE_PATTERNS__' => json_encode($this->getExcludePatterns(), JSON_UNESCAPED_SLASHES),
            '__BACKGROUND_SYNC_ENABLED__' => config('pwa.background_sync.enabled', true) ? 'true' : 'false',
            '__BACKGROUND_SYNC_ENDPOINT__' => config('pwa.background_sync.retry_endpoint', '/api/pwa/sync'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Get the current cache version, checking for override file.
     */
    public function getCacheVersion(): string
    {
        $overridePath = storage_path('pwa-cache-version.txt');

        if (file_exists($overridePath)) {
            return mb_trim(file_get_contents($overridePath));
        }

        return config('pwa.service_worker.cache_version', 'v1');
    }

    /**
     * Extract Vite asset URLs from build manifest for precaching.
     *
     * @return array<int, string>
     */
    public function getPrecacheUrls(): array
    {
        if (!config('pwa.service_worker.precache_vite_assets', true)) {
            return [];
        }

        $manifestPath = public_path('build/manifest.json');

        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (!is_array($manifest)) {
            return [];
        }

        $urls = [];

        foreach ($manifest as $entry) {
            if (isset($entry['file'])) {
                $urls[] = '/build/' . $entry['file'];
            }

            if (isset($entry['css']) && is_array($entry['css'])) {
                foreach ($entry['css'] as $css) {
                    $urls[] = '/build/' . $css;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Build cache strategy configuration for the service worker.
     *
     * @return array<int, array{pattern: string, strategy: string}>
     */
    private function getCacheStrategies(): array
    {
        $strategies = [];
        $configured = config('pwa.service_worker.cache_strategies', []);

        foreach ($configured as $pattern => $strategy) {
            $resolved = CacheStrategy::fromConfig($strategy);
            $strategies[] = [
                'pattern' => $pattern,
                'strategy' => $resolved->value,
            ];
        }

        return $strategies;
    }

    /**
     * Get URL patterns to exclude from service worker caching.
     *
     * @return array<int, string>
     */
    private function getExcludePatterns(): array
    {
        return config('pwa.service_worker.exclude_patterns', []);
    }
}
