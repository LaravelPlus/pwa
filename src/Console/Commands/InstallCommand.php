<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Console\Commands;

use Illuminate\Console\Command;

final class InstallCommand extends Command
{
    protected $signature = 'pwa:install
        {--migrations : Publish migrations}
        {--skills : Publish Claude skills}';

    protected $description = 'Install the PWA package assets and configuration.';

    public function handle(): int
    {
        $this->info('Installing LaravelPlus PWA...');

        $this->call('vendor:publish', [
            '--tag' => 'pwa-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'pwa-assets',
            '--force' => false,
        ]);

        if ($this->option('migrations')) {
            $this->call('vendor:publish', [
                '--tag' => 'pwa-migrations',
                '--force' => false,
            ]);
        }

        if ($this->option('skills')) {
            $this->call('vendor:publish', [
                '--tag' => 'pwa-skills',
                '--force' => false,
            ]);
        }

        $this->newLine();
        $this->components->info('PWA package installed successfully.');
        $this->newLine();

        $this->line('Add the following Blade directives to your root layout:');
        $this->newLine();
        $this->line('  In <head>:');
        $this->line('    <fg=green>@pwaHead</>');
        $this->newLine();
        $this->line('  Before </body>:');
        $this->line('    <fg=green>@pwaScripts</>');
        $this->newLine();

        $this->line('To generate VAPID keys for push notifications:');
        $this->line('  <fg=green>php artisan pwa:vapid</>');
        $this->newLine();

        $this->line('To generate app icons from a source image:');
        $this->line('  <fg=green>php artisan pwa:generate-icons --source=public/icon.png</>');

        return self::SUCCESS;
    }
}
