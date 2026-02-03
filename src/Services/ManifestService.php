<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Services;

final class ManifestService
{
    /**
     * Generate the web app manifest array from config.
     *
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        $config = config('pwa.manifest', []);

        $manifest = [
            'name' => $config['name'] ?? config('app.name'),
            'short_name' => $config['short_name'] ?? config('app.name'),
            'start_url' => $config['start_url'] ?? '/',
            'display' => $config['display'] ?? 'standalone',
            'orientation' => $config['orientation'] ?? 'any',
            'theme_color' => $config['theme_color'] ?? '#ffffff',
            'background_color' => $config['background_color'] ?? '#ffffff',
            'lang' => $config['lang'] ?? 'en',
            'dir' => $config['dir'] ?? 'ltr',
            'scope' => $config['scope'] ?? '/',
            'icons' => $config['icons'] ?? [],
        ];

        if (!empty($config['description'])) {
            $manifest['description'] = $config['description'];
        }

        if (!empty($config['categories'])) {
            $manifest['categories'] = $config['categories'];
        }

        if (!empty($config['screenshots'])) {
            $manifest['screenshots'] = $config['screenshots'];
        }

        if (!empty($config['shortcuts'])) {
            $manifest['shortcuts'] = $config['shortcuts'];
        }

        // Auto-discover icons from public/icons directory if none configured
        if (empty($manifest['icons'])) {
            $manifest['icons'] = $this->discoverIcons();
        }

        return array_filter($manifest, fn (mixed $value): bool => $value !== null && $value !== []);
    }

    /**
     * Generate the manifest as a JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Auto-discover icons from the public/icons directory.
     *
     * @return array<int, array{src: string, sizes: string, type: string}>
     */
    private function discoverIcons(): array
    {
        $iconsPath = public_path('icons');
        $icons = [];

        if (!is_dir($iconsPath)) {
            return $icons;
        }

        $files = glob($iconsPath . '/icon-*.png');

        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/icon-(\d+x\d+)\.png/', $filename, $matches)) {
                $icons[] = [
                    'src' => '/icons/' . $filename,
                    'sizes' => $matches[1],
                    'type' => 'image/png',
                ];
            }
        }

        return $icons;
    }
}
