<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_shows_current_store_status(): void
    {
        Setting::create(['key' => 'store_status', 'value' => 'closed']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Kedai sedang tutup');
    }

    public function test_admin_can_change_store_status(): void
    {
        Setting::create(['key' => 'store_status', 'value' => 'open']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.settings.update'), ['store_status' => 'closed'])
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success', 'Status kedai berhasil diperbarui.');

        $this->assertDatabaseHas('settings', [
            'key' => 'store_status',
            'value' => 'closed',
        ]);
    }

    public function test_guest_cannot_change_store_status(): void
    {
        $this->post(route('admin.settings.update'), ['store_status' => 'closed'])
            ->assertRedirect(route('login'));
    }

    public function test_admin_cannot_save_an_unsupported_store_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.settings.update'), ['store_status' => 'unknown'])
            ->assertSessionHasErrors('store_status');

        $this->assertDatabaseCount('settings', 0);
    }
}
