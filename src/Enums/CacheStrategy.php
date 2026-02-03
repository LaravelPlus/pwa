<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Enums;

enum CacheStrategy: string
{
    case CacheFirst = 'cache-first';
    case NetworkFirst = 'network-first';
    case StaleWhileRevalidate = 'stale-while-revalidate';
    case CacheOnly = 'cache-only';
    case NetworkOnly = 'network-only';

    /**
     * Resolve a strategy from a config string value.
     */
    public static function fromConfig(string $value): self
    {
        return match ($value) {
            'CacheFirst', 'cache-first' => self::CacheFirst,
            'NetworkFirst', 'network-first' => self::NetworkFirst,
            'StaleWhileRevalidate', 'stale-while-revalidate' => self::StaleWhileRevalidate,
            'CacheOnly', 'cache-only' => self::CacheOnly,
            'NetworkOnly', 'network-only' => self::NetworkOnly,
            default => self::NetworkFirst,
        };
    }
}
