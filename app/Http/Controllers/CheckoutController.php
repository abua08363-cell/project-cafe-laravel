<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const TAX_RATE = 0.11;

    private const CLOSED_MESSAGE = 'Maaf, cafe kami sedang tutup. Silakan pesan kembali saat cafe sudah buka.';

    public function index(): View|RedirectResponse
    {
        if (! $this->storeIsOpen()) {
            return redirect()
                ->route('home')
                ->with('error', self::CLOSED_MESSAGE);
        }

        return view('cart.checkout');
    }

    public function process(Request $request): JsonResponse
    {
        if (! $this->storeIsOpen()) {
            return response()->json([
                'status' => 'error',
                'message' => self::CLOSED_MESSAGE,
            ], 403);
        }

        $data = $request->validate([
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.id' => ['required', 'integer'],
            'cart.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'customer_name' => ['required', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Harga SELALU diambil dari database, bukan dari browser (browser bisa dimanipulasi)
        $ids = collect($data['cart'])->pluck('id')->unique()->all();
        $menus = Menu::whereIn('id', $ids)->get()->keyBy('id');

        if ($menus->count() !== count($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ada menu yang sudah tidak tersedia. Muat ulang halaman dan pilih ulang.',
            ], 422);
        }

        $order = DB::transaction(function () use ($data, $menus): ?Order {
            if (! $this->storeIsOpen(true)) {
                return null;
            }

            $subtotal = 0.0;
            $lines = [];

            foreach ($data['cart'] as $row) {
                $menu = $menus[$row['id']];
                $lineTotal = (float) $menu->price * $row['qty']; // Diperbaiki dari tanda minus (-) ke asterisk (*)
                $subtotal += $lineTotal;

                $lines[] = [
                    'menu_id' => $menu->id,
                    'price' => $menu->price,
                    'quantity' => $row['qty'],
                ];
            }

            $tax = round($subtotal * self::TAX_RATE); // Diperbaiki dari tanda minus (-) ke asterisk (*)

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'table_number' => $data['table_number'],
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_price' => $subtotal + $tax,
                'status' => 'pending',
            ]);

            $order->items()->createMany($lines);

            return $order;
        });

        if ($order === null) {
            return response()->json([
                'status' => 'error',
                'message' => self::CLOSED_MESSAGE,
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('checkout.success', $order),
        ]);
    }

    public function success(Order $order): View
    {
        $order->load('items.menu', 'review');
        $subtotal = $order->items->sum(fn ($item): float => (float) $item->price * $item->quantity); // Diperbaiki
        
        $orderItems = $order->items->map(fn ($item): array => [
            'menu_id' => $item->menu_id,
            'name' => $item->menu?->name ?? 'Menu tidak tersedia',
            'image' => $item->menu?->image,
            'price' => $item->price,
            'quantity' => $item->quantity,
        ]);

        return view('cart.success', [
            'order' => [
                'id' => $order->id,
                'table_number' => $order->table_number,
                'payment_method' => $order->payment_method,
                'total' => $order->total_price,
                'subtotal' => $subtotal,
                'tax' => (float) $order->total_price - $subtotal, // Diperbaiki dari minus yang salah posisi
                'payment_status' => 'pending',
                'created_at' => $order->created_at?->toDateTimeString(),
            ],
            'order_items' => $orderItems,
            'existingReview' => $order->review,
        ]);
    }

    public function storeReview(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'aspects' => ['sometimes', 'array:rasa,pelayanan,kecepatan,suasana'],
            'aspects.rasa' => ['nullable', 'integer', 'between:1,5'],
            'aspects.pelayanan' => ['nullable', 'integer', 'between:1,5'],
            'aspects.kecepatan' => ['nullable', 'integer', 'between:1,5'],
            'aspects.suasana' => ['nullable', 'integer', 'between:1,5'],
            'items' => ['sometimes', 'array', 'max:50'],
            'items.*' => ['required', 'integer', 'between:1,5'],
            'tags' => ['sometimes', 'array', 'max:5'],
            'tags.*' => ['required', 'string', 'max:80'],
            'comment' => ['nullable', 'string', 'max:300'],
            'recommend' => ['nullable', 'boolean'],
            'anonymous' => ['sometimes', 'boolean'],
        ]);

        $orderedMenuIds = $order->items()->pluck('menu_id')->map(fn ($id): int => (int) $id)->all();
        $submittedMenuIds = array_map('intval', array_keys($validated['items'] ?? []));

        if (array_diff($submittedMenuIds, $orderedMenuIds) !== []) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rating menu hanya dapat diberikan untuk menu dalam pesanan ini.',
            ], 422);
        }

        $reviewAlreadyExists = DB::transaction(function () use ($order, $validated): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->review()->exists()) {
                return true;
            }

            $isAnonymous = $validated['anonymous'] ?? false;

            $lockedOrder->review()->create([
                'name' => $isAnonymous ? 'Anonim' : $lockedOrder->customer_name,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'aspects' => $validated['aspects'] ?? null,
                'item_ratings' => $validated['items'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'recommend' => $validated['recommend'] ?? null,
                'anonymous' => $isAnonymous,
                'status' => 'paid',
            ]);

            return false;
        });

        if ($reviewAlreadyExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ulasan untuk pesanan ini sudah pernah dikirim.',
            ], 409);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Terima kasih, ulasanmu berhasil disimpan.',
        ]);
    }

    private function storeIsOpen(bool $forUpdate = false): bool
    {
        $setting = Setting::query()->where('key', 'store_status');

        if ($forUpdate) {
            $setting->lockForUpdate();
        }

        return $setting->value('value') !== 'closed';
    }
}