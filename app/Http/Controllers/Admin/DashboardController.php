<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Padanan nilai status di database ke 4 kelompok yang tampil di dashboard.
     * Tambahkan di sini kalau database kamu memakai istilah lain.
     */
    private const STATUS_MAP = [
        'completed'  => 'completed',
        'complete'   => 'completed',
        'paid'       => 'completed',
        'selesai'    => 'completed',
        'lunas'      => 'completed',

        'processing' => 'processing',
        'process'    => 'processing',
        'diproses'   => 'processing',

        'pending'    => 'pending',
        'menunggu'   => 'pending',

        'cancelled'  => 'cancelled',
        'canceled'   => 'cancelled',
        'dibatalkan' => 'cancelled',
    ];

    public function index()
    {
        // 5 pesanan terbaru: hanya untuk tabel "Pesanan Masuk Terbaru"
        $recent_orders = Order::latest()->take(5)->get()->map(function ($order) {
            return [
                'customer_name'  => $order->customer_name,
                'table_number'   => $order->table_number,
                'total'          => (float) $order->total_price,
                'payment_status' => $order->status,
                'status'         => $order->status,
            ];
        })->toArray();

        // Hitung status dari SEMUA pesanan (bukan dari 5 baris di atas)
        $status_counts = [
            'completed'  => 0,
            'processing' => 0,
            'pending'    => 0,
            'cancelled'  => 0,
        ];

        $rows = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        foreach ($rows as $row) {
            $key = strtolower(trim((string) $row->status));
            $group = self::STATUS_MAP[$key] ?? 'pending'; // status tak dikenal dianggap menunggu
            $status_counts[$group] += (int) $row->total;
        }

        // Nilai pesanan dari semua pesanan yang tidak dibatalkan
        $valid = Order::where(function ($q) {
            $q->whereNull('status')
              ->orWhereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancelled', 'canceled', 'dibatalkan']);
        });

        $order_value = (float) (clone $valid)->sum('total_price');
        $valid_count = (clone $valid)->count();
        $order_avg   = $valid_count > 0 ? $order_value / $valid_count : 0;

        return view('admin.dashboard.index', [
            'total_menu'     => Menu::count(),
            'total_users'    => User::count(),
            'total_category' => Category::count(),
            'total_orders'   => Order::count(),
            'recent_orders'  => $recent_orders,

            // Baru: semuanya dihitung dari seluruh pesanan
            'status_counts'  => $status_counts,
            'order_value'    => $order_value,
            'order_avg'      => $order_avg,
        ]);
    }
}