<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil semua pengaturan kedai dari tabel settings sebagai array [key => value]
        $settings = Setting::query()->pluck('value', 'key')->all();

        return view('home', [
            'menus'       => Menu::with('category')->latest()->get(),
            'categories'  => Category::orderBy('name')->get(),
            'settings'    => $settings,
            // Status buka/tutup dikontrol dari Admin > Settings
            'storeStatus' => $settings['store_status'] ?? 'open',
        ]);
    }
}