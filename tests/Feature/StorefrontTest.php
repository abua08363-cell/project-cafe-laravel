<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_all_menus_and_categories(): void
    {
        $coffee = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        $pastry = Category::create(['name' => 'Pastry', 'slug' => 'pastry']);
        Menu::create(['category_id' => $coffee->id, 'name' => 'Cappuccino', 'price' => 28000]);
        Menu::create(['category_id' => $pastry->id, 'name' => 'Croissant', 'price' => 22000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cappuccino')
            ->assertSee('Croissant')
            ->assertSee('Coffee')
            ->assertSee('Pastry');
    }

    public function test_homepage_shows_closed_message_and_disables_order_actions(): void
    {
        Setting::create(['key' => 'store_status', 'value' => 'closed']);
        $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);
        Menu::create(['category_id' => $category->id, 'name' => 'Cappuccino', 'price' => 28000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Maaf, cafe kami sedang tutup.')
            ->assertSee('disabled aria-label="Maaf, cafe kami sedang tutup"', false);
    }
}
