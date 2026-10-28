<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'menus'      => Menu::with('category')->latest()->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}