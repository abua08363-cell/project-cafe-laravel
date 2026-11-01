<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        // Seluruh pesanan beserta item menu dan ulasannya
        $orders = Order::with(['items.menu', 'review'])
            ->latest()
            ->get();

        // 1. Total transaksi
        $totalTransaksi = $orders->count();

        // 2. Total pendapatan: semua pesanan kecuali yang dibatalkan.
        //    Kalau ingin hanya yang selesai, ganti kondisinya menjadi:
        //    strtolower((string) $order->status) === 'completed'
        $totalPendapatan = $orders
            ->filter(fn ($order): bool => strtolower((string) $order->status) !== 'cancelled')
            ->sum('total_price');

        // 3. Rating dihitung dari ulasan yang sama dengan yang tampil di tabel,
        //    jadi angka kartu selalu cocok dengan isi tabel.
        $reviews = $orders->pluck('review')->filter();

        $totalReviewCount = $reviews->count();
        $avgRating = $totalReviewCount > 0 ? (float) $reviews->avg('rating') : 0.0;

        return view('admin.orders.history', [
            'orders'           => $orders,
            'totalTransaksi'   => $totalTransaksi,
            'totalPendapatan'  => $totalPendapatan,
            'avgRating'        => $avgRating,
            'totalReviewCount' => $totalReviewCount,
            // Nama lama tetap dikirim supaya view lain yang memakainya tidak error
            'totalReviews'     => $totalReviewCount,
        ]);
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