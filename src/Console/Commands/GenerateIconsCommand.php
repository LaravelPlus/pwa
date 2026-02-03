<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Console\Commands;

use Illuminate\Console\Command;

final class GenerateIconsCommand extends Command
{
    protected $signature = 'pwa:generate-icons
        {--source= : Path to the source PNG image}
        {--output= : Output directory for generated icons}';

    protected $description = 'Generate PWA icon set from a source PNG image.';

    public function handle(): int
    {
        $source = $this->option('source') ?? config('pwa.icons.source', public_path('icon.png'));
        $outputDir = $this->option('output') ?? config('pwa.icons.output_dir', public_path('icons'));
        $sizes = config('pwa.icons.sizes', [72, 96, 128, 144, 152, 192, 384, 512]);

        if (!file_exists($source)) {
            $this->components->error("Source image not found: {$source}");

            return self::FAILURE;
        }

        if (!extension_loaded('gd')) {
            $this->components->error('The GD extension is required for icon generation.');

            return self::FAILURE;
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $imageInfo = getimagesize($source);
        if ($imageInfo === false) {
            $this->components->error('Could not read source image dimensions.');

            return self::FAILURE;
        }

        $sourceImage = match ($imageInfo[2]) {
            IMAGETYPE_PNG => imagecreatefrompng($source),
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_WEBP => imagecreatefromwebp($source),
            default => false,
        };

        if ($sourceImage === false) {
            $this->components->error('Unsupported image format. Use PNG, JPEG, or WebP.');

            return self::FAILURE;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        foreach ($sizes as $size) {
            $resized = imagecreatetruecolor($size, $size);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);

            imagecopyresampled(
                $resized,
                $sourceImage,
                0,
                0,
                0,
                0,
                $size,
                $size,
                $sourceWidth,
                $sourceHeight,
            );

            $filename = "icon-{$size}x{$size}.png";
            imagepng($resized, $outputDir . '/' . $filename);
            imagedestroy($resized);

            $this->line("  Generated: {$filename}");
        }

        imagedestroy($sourceImage);

        $this->newLine();
        $this->components->info(count($sizes) . ' icons generated in: ' . $outputDir);

        return self::SUCCESS;
    }
}
