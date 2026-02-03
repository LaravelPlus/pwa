<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackgroundSyncController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string'],
            'method' => ['required', 'string', 'in:GET,POST,PUT,PATCH,DELETE'],
            'headers' => ['sometimes', 'array'],
            'body' => ['sometimes', 'nullable'],
        ]);

        $url = $request->input('url');
        $parsedUrl = parse_url($url);

        // Validate the URL is internal (same host or relative)
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $appHost) {
            return response()->json(['message' => 'Only internal URLs are allowed.'], 403);
        }

        $path = $parsedUrl['path'] ?? '/';
        $query = $parsedUrl['query'] ?? '';

        $internalRequest = Request::create(
            $path . ($query ? '?' . $query : ''),
            $request->input('method', 'GET'),
            is_array($request->input('body')) ? $request->input('body') : [],
            $request->cookies->all(),
            [],
            $request->server->all(),
            is_string($request->input('body')) ? $request->input('body') : null,
        );

        // Forward auth from the original request
        $internalRequest->setUserResolver(fn () => $request->user());

        $headers = $request->input('headers', []);
        foreach ($headers as $key => $value) {
            $internalRequest->headers->set($key, $value);
        }

        $response = app()->handle($internalRequest);

        return response()->json([
            'status' => $response->getStatusCode(),
            'message' => 'Request replayed successfully.',
        ]);
    }
}
