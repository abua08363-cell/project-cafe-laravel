<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        // Ambil seluruh data pesanan beserta relasi item menu dan review
        $orders = Order::with(['items.menu', 'review'])
            ->withCount('items')
            ->latest()
            ->get();

        // 1. Total Transaksi (jumlah seluruh pesanan)
        $totalTransaksi = $orders->count();

        // 2. Total Pendapatan (menghitung total_price dari seluruh pesanan atau yang statusnya selesai/completed)
        // Jika ingin menghitung dari semua pesanan yang masuk, gunakan sum('total_price') langsung
        $totalPendapatan = $orders->sum('total_price');

        // 3. Rata-rata Rating dan jumlah ulasan
        $avgRating = Review::avg('rating') ?? 0;
        $totalReviews = Review::count();

        return view('admin.orders.history', compact(
            'orders', 
            'totalTransaksi', 
            'totalPendapatan', 
            'avgRating', 
            'totalReviews'
        ));
    }

    public function show(Order $order): View
    {
        $order->load('items.menu', 'review');

        return view('admin.orders.detail', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,completed,cancelled'],
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Pesanan berhasil dihapus.');
    }
}