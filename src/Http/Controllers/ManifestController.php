<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Controllers;

use Illuminate\Http\JsonResponse;
use LaravelPlus\Pwa\Services\ManifestService;

final class ManifestController
{
    public function __construct(
        private(set) ManifestService $manifestService,
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json(
            $this->manifestService->generate(),
        )->header('Content-Type', 'application/manifest+json');
    }
}
