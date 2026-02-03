<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

final class VapidCommand extends Command
{
    protected $signature = 'pwa:vapid';

    protected $description = 'Generate VAPID keys for push notifications and write them to .env.';

    public function handle(): int
    {
        $keys = $this->generateVapidKeys();

        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->components->error('.env file not found.');

            return self::FAILURE;
        }

        $envContent = file_get_contents($envPath);

        $envContent = $this->setEnvValue($envContent, 'VAPID_PUBLIC_KEY', $keys['publicKey']);
        $envContent = $this->setEnvValue($envContent, 'VAPID_PRIVATE_KEY', $keys['privateKey']);

        file_put_contents($envPath, $envContent);

        $this->components->info('VAPID keys generated and written to .env.');
        $this->newLine();
        $this->line('Public Key:');
        $this->line("  {$keys['publicKey']}");
        $this->newLine();
        $this->line('Private Key:');
        $this->line("  {$keys['privateKey']}");

        return self::SUCCESS;
    }

    /**
     * Generate a VAPID keypair using OpenSSL directly.
     *
     * @return array{publicKey: string, privateKey: string}
     */
    private function generateVapidKeys(): array
    {
        $opensslConfig = $this->findOpensslConfig();

        $options = [
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];

        if ($opensslConfig !== null) {
            $options['config'] = $opensslConfig;
        }

        $key = openssl_pkey_new($options);

        if ($key === false) {
            throw new RuntimeException('Failed to generate EC key: ' . openssl_error_string());
        }

        $details = openssl_pkey_get_details($key);

        if ($details === false || !isset($details['ec']['d'], $details['ec']['x'], $details['ec']['y'])) {
            throw new RuntimeException('Failed to extract EC key details.');
        }

        // Private key: base64url-encoded 'd' parameter
        $privateKey = $this->base64urlEncode($details['ec']['d']);

        // Public key: 0x04 || x || y (uncompressed point format), base64url-encoded
        $publicKey = $this->base64urlEncode(
            chr(4) . str_pad($details['ec']['x'], 32, chr(0), STR_PAD_LEFT)
                    . str_pad($details['ec']['y'], 32, chr(0), STR_PAD_LEFT)
        );

        return [
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
        ];
    }

    private function findOpensslConfig(): ?string
    {
        $candidates = [
            '/etc/ssl/openssl.cnf',
            '/etc/pki/tls/openssl.cnf',
            '/usr/lib/ssl/openssl.cnf',
            '/usr/local/etc/openssl/openssl.cnf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*/m";

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, "{$key}={$value}", $content);
        }

        return $content . "\n{$key}={$value}";
    }
}
