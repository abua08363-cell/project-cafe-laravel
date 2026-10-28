<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_cafe_redirects_checkout_to_homepage_with_a_message(): void
    {
        Setting::create(['key' => 'store_status', 'value' => 'closed']);

        $this->get(route('checkout.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Maaf, cafe kami sedang tutup. Silakan pesan kembali saat cafe sudah buka.');
    }

    public function test_closed_cafe_rejects_order_submission_without_creating_an_order(): void
    {
        Setting::create(['key' => 'store_status', 'value' => 'closed']);

        $this->postJson(route('checkout.process'), [
            'cart' => [['id' => 1, 'qty' => 1]],
            'customer_name' => 'Tamu BrewNest',
            'table_number' => 'Meja 1',
            'payment_method' => 'Tunai',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Maaf, cafe kami sedang tutup. Silakan pesan kembali saat cafe sudah buka.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_open_cafe_accepts_order_and_persists_menu_items(): void
    {
        $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $menu = Menu::create(['category_id' => $category->id, 'name' => 'Cappuccino', 'price' => 28000]);

        $response = $this->postJson(route('checkout.process'), [
            'cart' => [['id' => $menu->id, 'qty' => 2]],
            'customer_name' => 'Tamu BrewNest',
            'table_number' => 'Meja 1',
            'payment_method' => 'Tunai',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('redirect_url', route('checkout.success', Order::first()));

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Tamu BrewNest',
            'total_price' => 62160,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'menu_id' => $menu->id,
            'quantity' => 2,
            'price' => 28000,
        ]);
    }
}
