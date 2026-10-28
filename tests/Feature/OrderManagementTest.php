<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_order_history_and_details(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'customer_name' => 'Pelanggan BrewNest',
            'phone' => '081234567890',
            'total_price' => 45000,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Pelanggan BrewNest')
            ->assertSee('Riwayat Pesanan');

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Pesanan #'.$order->id);
    }

    public function test_order_status_can_be_updated_to_a_supported_value(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'customer_name' => 'Pelanggan BrewNest',
            'total_price' => 45000,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    public function test_order_history_displays_a_submitted_review(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'customer_name' => 'Pelanggan BrewNest',
            'total_price' => 45000,
            'status' => 'pending',
        ]);
        $order->review()->create([
            'name' => 'Pelanggan BrewNest',
            'rating' => 5,
            'comment' => 'Kopinya enak.',
        ]);

        $this->actingAs($user)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Kopinya enak.')
            ->assertSee('5/5');
    }
}
