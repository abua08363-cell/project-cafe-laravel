<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $settings = Setting::query()->pluck('value', 'key')->all();

        return view('welcome', [
            'menus' => Menu::query()->with('category')->latest()->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'settings' => $settings,
            'storeStatus' => $settings['store_status'] ?? 'open',
        ]);
    }
}
