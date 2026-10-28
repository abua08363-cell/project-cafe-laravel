<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_review_for_their_order(): void
    {
        [$order, $menu] = $this->createOrderWithMenu();

        $response = $this->postJson(route('checkout.review', $order), [
            'rating' => 5,
            'aspects' => ['rasa' => 4],
            'items' => [$menu->id => 5],
            'tags' => ['Rasa mantap'],
            'comment' => 'Kopinya enak.',
            'recommend' => true,
            'anonymous' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('message', 'Terima kasih, ulasanmu berhasil disimpan.');

        $this->assertDatabaseHas('order_reviews', [
            'order_id' => $order->id,
            'name' => 'Anonim',
            'rating' => 5,
            'comment' => 'Kopinya enak.',
            'recommend' => true,
            'anonymous' => true,
        ]);

        $review = OrderReview::query()->where('order_id', $order->id)->firstOrFail();
        $this->assertSame(['rasa' => 4], $review->aspects);
        $this->assertSame([$menu->id => 5], $review->item_ratings);
        $this->assertSame(['Rasa mantap'], $review->tags);
    }

    public function test_invalid_rating_is_rejected_without_saving_a_review(): void
    {
        [$order] = $this->createOrderWithMenu();

        $this->postJson(route('checkout.review', $order), ['rating' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rating']);

        $this->assertDatabaseCount('order_reviews', 0);
    }

    public function test_customer_cannot_rate_a_menu_not_in_the_order(): void
    {
        [$order] = $this->createOrderWithMenu();
        $category = Category::query()->firstOrFail();
        $otherMenu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Mocha',
            'price' => 32000,
        ]);

        $this->postJson(route('checkout.review', $order), [
            'rating' => 4,
            'items' => [$otherMenu->id => 4],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Rating menu hanya dapat diberikan untuk menu dalam pesanan ini.');

        $this->assertDatabaseCount('order_reviews', 0);
    }

    public function test_order_can_only_be_reviewed_once(): void
    {
        [$order] = $this->createOrderWithMenu();
        $payload = ['rating' => 4];

        $this->postJson(route('checkout.review', $order), $payload)->assertOk();

        $this->postJson(route('checkout.review', $order), $payload)
            ->assertConflict()
            ->assertJsonPath('message', 'Ulasan untuk pesanan ini sudah pernah dikirim.');

        $this->assertDatabaseCount('order_reviews', 1);
    }

    public function test_order_receipt_exposes_menu_ids_for_item_ratings(): void
    {
        [$order, $menu] = $this->createOrderWithMenu();

        $this->get(route('checkout.success', $order))
            ->assertOk()
            ->assertSee('data-item="'.$menu->id.'"', false)
            ->assertSee('Kopi pesanan');
    }

    /**
     * @return array{0: Order, 1: Menu}
     */
    private function createOrderWithMenu(): array
    {
        $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi pesanan',
            'price' => 28000,
        ]);
        $order = Order::create([
            'customer_name' => 'Pelanggan BrewNest',
            'total_price' => 31100,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'menu_id' => $menu->id,
            'quantity' => 1,
            'price' => 28000,
        ]);

        return [$order, $menu];
    }
}
