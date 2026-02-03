<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePushSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string', 'url'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'content_encoding' => ['sometimes', 'string', 'in:aesgcm,aes128gcm'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'endpoint.required' => 'A push subscription endpoint is required.',
            'endpoint.url' => 'The push subscription endpoint must be a valid URL.',
            'keys.required' => 'Push subscription keys are required.',
            'keys.p256dh.required' => 'The p256dh key is required.',
            'keys.auth.required' => 'The auth key is required.',
        ];
    }
}
