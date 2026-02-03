<?php

declare(strict_types=1);

namespace LaravelPlus\Pwa\Tests\Feature;

use App\Constants\RoleNames;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class AdminPwaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => RoleNames::SUPER_ADMIN]);
        Role::create(['name' => RoleNames::ADMIN]);
        Role::create(['name' => RoleNames::USER]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(RoleNames::ADMIN);

        $this->user = User::factory()->create();
        $this->user->assignRole(RoleNames::USER);
    }

    public function test_guest_cannot_access_pwa_admin(): void
    {
        $response = $this->get('/admin/pwa');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_pwa_admin(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/pwa');

        $response->assertForbidden();
    }

    public function test_admin_can_access_pwa_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pwa');

        $response->assertOk();
    }

    public function test_admin_can_update_pwa_settings(): void
    {
        $response = $this->actingAs($this->admin)->patch('/admin/pwa', [
            'name' => 'Updated App Name',
            'short_name' => 'Updated',
            'theme_color' => '#ff0000',
            'background_color' => '#000000',
            'display' => 'standalone',
            'orientation' => 'portrait',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'PWA settings updated.');
    }

    public function test_update_validates_hex_colors(): void
    {
        $response = $this->actingAs($this->admin)->patch('/admin/pwa', [
            'name' => 'App',
            'short_name' => 'App',
            'theme_color' => 'not-a-color',
            'background_color' => '#fff',
            'display' => 'standalone',
            'orientation' => 'any',
        ]);

        $response->assertSessionHasErrors(['theme_color', 'background_color']);
    }

    public function test_update_validates_display_mode(): void
    {
        $response = $this->actingAs($this->admin)->patch('/admin/pwa', [
            'name' => 'App',
            'short_name' => 'App',
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'invalid-mode',
            'orientation' => 'any',
        ]);

        $response->assertSessionHasErrors('display');
    }
}
