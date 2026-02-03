<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Pwa\Contracts\PushSubscriptionRepositoryInterface;
use LaravelPlus\Pwa\Http\Requests\Admin\UpdatePwaSettingsRequest;

final class PwaController
{
    public function __construct(
        private(set) PushSubscriptionRepositoryInterface $subscriptionRepository,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $settings = [
            'name' => config('pwa.manifest.name'),
            'short_name' => config('pwa.manifest.short_name'),
            'description' => config('pwa.manifest.description'),
            'theme_color' => config('pwa.manifest.theme_color'),
            'background_color' => config('pwa.manifest.background_color'),
            'display' => config('pwa.manifest.display'),
            'orientation' => config('pwa.manifest.orientation'),
            'push_enabled' => config('pwa.push.enabled'),
            'background_sync_enabled' => config('pwa.background_sync.enabled'),
        ];

        $stats = [
            'total_subscriptions' => $this->subscriptionRepository->count(),
        ];

        return Inertia::render('admin/Pwa/Index', [
            'settings' => $settings,
            'stats' => $stats,
        ]);
    }

    public function update(UpdatePwaSettingsRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validated();

        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            $settingMap = [
                'name' => 'pwa.manifest.name',
                'short_name' => 'pwa.manifest.short_name',
                'description' => 'pwa.manifest.description',
                'theme_color' => 'pwa.manifest.theme_color',
                'background_color' => 'pwa.manifest.background_color',
                'display' => 'pwa.manifest.display',
                'orientation' => 'pwa.manifest.orientation',
                'push_enabled' => 'pwa.push.enabled',
                'background_sync_enabled' => 'pwa.background_sync.enabled',
            ];

            foreach ($validated as $key => $value) {
                if (isset($settingMap[$key])) {
                    \LaravelPlus\GlobalSettings\Models\Setting::set(
                        $settingMap[$key],
                        is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    );
                }
            }
        }

        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::log('pwa.settings.updated', null, null, $validated);
        }

        return redirect()
            ->route('admin.pwa.index')
            ->with('status', 'PWA settings updated.');
    }

    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        if (!$user || !array_any(['super-admin', 'admin'], fn (string $role): bool => $user->hasRole($role))) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }
}
