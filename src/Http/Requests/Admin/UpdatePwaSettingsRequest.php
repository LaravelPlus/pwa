<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePwaSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && array_any(['super-admin', 'admin'], fn (string $role): bool => $user->hasRole($role));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'theme_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'display' => ['required', 'string', 'in:standalone,fullscreen,minimal-ui,browser'],
            'orientation' => ['required', 'string', 'in:any,natural,landscape,portrait'],
            'push_enabled' => ['sometimes', 'boolean'],
            'background_sync_enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'theme_color.regex' => 'The theme color must be a valid hex color (e.g., #ffffff).',
            'background_color.regex' => 'The background color must be a valid hex color (e.g., #ffffff).',
        ];
    }
}
