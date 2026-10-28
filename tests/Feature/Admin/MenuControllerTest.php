<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_renders_menu_with_its_category(): void
    {
        $category = Category::create([
            'name' => 'Coffee',
            'slug' => 'coffee',
        ]);
        Menu::create([
            'category_id' => $category->id,
            'name' => 'Cappuccino',
            'price' => 28000,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.menu.index'))
            ->assertOk()
            ->assertSee('Cappuccino')
            ->assertSee('Coffee');
    }

    public function test_edit_form_uses_model_fields_and_category_ids(): void
    {
        $category = Category::create([
            'name' => 'Coffee',
            'slug' => 'coffee',
        ]);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Flat White',
            'price' => 25000,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.menu.edit', $menu))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('value="Flat White"', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('value="'.$category->id.'"', false)
            ->assertSee('Coffee');
    }

    public function test_menu_can_be_updated_using_the_edit_form_fields(): void
    {
        $category = Category::create([
            'name' => 'Coffee',
            'slug' => 'coffee',
        ]);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Flat White',
            'price' => 25000,
        ]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.menu.update', $menu), [
                'category_id' => $category->id,
                'name' => 'Iced Latte',
                'price' => 30000,
                'description' => 'Kopi susu dingin',
            ])
            ->assertRedirect(route('admin.menu.index'));

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'category_id' => $category->id,
            'name' => 'Iced Latte',
            'price' => 30000,
        ]);
    }
}
