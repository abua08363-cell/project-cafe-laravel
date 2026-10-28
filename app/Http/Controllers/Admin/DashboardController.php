<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use App\Models\Category;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil 5 pesanan terbaru untuk tabel dan grafik
        $recent_orders = Order::latest()->take(5)->get()->map(function($order) {
            return [
                'customer_name' => $order->customer_name,
                'table_number' => $order->table_number,
                'total' => $order->total_price,
                'payment_status' => $order->status,
                'status' => $order->status,
            ];
        })->toArray();

        return view('admin.dashboard.index', [
            'total_menu'     => Menu::count(),
            'total_users'    => User::count(),
            'total_category' => Category::count(),
            'total_orders'   => Order::count(),
            'recent_orders'  => $recent_orders,
        ]);
    }
}