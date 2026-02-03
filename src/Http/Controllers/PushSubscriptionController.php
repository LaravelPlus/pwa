<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Pwa\Contracts\PwaServiceInterface;
use LaravelPlus\Pwa\Http\Requests\StorePushSubscriptionRequest;

final class PushSubscriptionController
{
    public function __construct(
        private(set) PwaServiceInterface $pwaService,
    ) {}

    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        $this->pwaService->subscribe(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json(['message' => 'Subscribed successfully.'], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $this->pwaService->unsubscribe($request->input('endpoint'));

        return response()->json(null, 204);
    }
}
