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
    /** Dipakai hanya kalau "Pajak (%)" di halaman Settings kosong atau tidak valid. */
    private const DEFAULT_TAX_PERCENT = 11.0;

    private const CLOSED_MESSAGE = 'Maaf, cafe kami sedang tutup. Silakan pesan kembali saat cafe sudah buka.';

    private const NO_PAYMENT_MESSAGE = 'Maaf, saat ini belum ada metode pembayaran yang tersedia. Silakan hubungi kasir.';

    private const PAYMENT_UNAVAILABLE_MESSAGE = 'Metode pembayaran yang kamu pilih sedang tidak tersedia. Muat ulang halaman lalu pilih metode lain.';

    /**
     * Metode pembayaran => nama pengaturan di halaman Settings admin.
     *   cash     = "Bayar di Kasir (Cash)"
     *   qris     = "QRIS / E-Wallet"
     *   transfer = "Transfer Bank" (di halaman checkout tampil sebagai Debit / Kredit)
     */
    private const PAYMENT_SETTINGS = [
        'cash'     => 'pay_cash',
        'qris'     => 'pay_qris',
        'transfer' => 'pay_transfer',
    ];

    /**
     * Kata kunci untuk mengenali metode dari teks yang dikirim halaman checkout.
     * Kalau nanti ada nama bank/metode baru, tambahkan di sini.
     */
    private const PAYMENT_KEYWORDS = [
        'cash'     => ['cash', 'tunai'],
        'qris'     => ['qris', 'e-wallet', 'ewallet'],
        'transfer' => ['transfer', 'debit', 'kredit', 'credit', 'bank', 'bca', 'mandiri', 'bri', 'bni', 'visa', 'mastercard'],
    ];

    public function index(): View|RedirectResponse
    {
        if (! $this->storeIsOpen()) {
            return redirect()
                ->route('home')
                ->with('error', self::CLOSED_MESSAGE);
        }

        $enabledMethods = $this->enabledPaymentMethods();

        if ($enabledMethods === []) {
            return redirect()
                ->route('home')
                ->with('error', self::NO_PAYMENT_MESSAGE);
        }

        return view('cart.checkout', [
            // Contoh isi: ['cash', 'transfer']
            'enabledMethods' => $enabledMethods,
            // Contoh isi: 11 atau 10.5 (diatur di halaman Settings)
            'taxPercentage' => $this->taxPercentage(),
        ]);
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

        // Metode pembayaran harus sesuai pengaturan admin (cek di server, bukan hanya di tampilan)
        $methodKey = $this->resolvePaymentMethod($data['payment_method']);

        if ($methodKey === null || ! in_array($methodKey, $this->enabledPaymentMethods(), true)) {
            return response()->json([
                'status' => 'error',
                'message' => self::PAYMENT_UNAVAILABLE_MESSAGE,
            ], 422);
        }

        // Harga SELALU diambil dari database, bukan dari browser (browser bisa dimanipulasi)
        $ids = collect($data['cart'])->pluck('id')->unique()->all();
        $menus = Menu::whereIn('id', $ids)->get()->keyBy('id');

        if ($menus->count() !== count($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ada menu yang sudah tidak tersedia. Muat ulang halaman dan pilih ulang.',
            ], 422);
        }

        // Pajak diambil dari Settings (bukan dari browser)
        $taxRate = $this->taxPercentage() / 100;

        $order = DB::transaction(function () use ($data, $menus, $taxRate): ?Order {
            if (! $this->storeIsOpen(true)) {
                return null;
            }

            $subtotal = 0.0;
            $lines = [];

            foreach ($data['cart'] as $row) {
                $menu = $menus[$row['id']];
                $lineTotal = (float) $menu->price * $row['qty'];
                $subtotal += $lineTotal;

                $lines[] = [
                    'menu_id' => $menu->id,
                    'price' => $menu->price,
                    'quantity' => $row['qty'],
                ];
            }

            $tax = round($subtotal * $taxRate);

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
        $subtotal = $order->items->sum(fn ($item): float => (float) $item->price * $item->quantity);

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
                'tax' => (float) $order->total_price - $subtotal,
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

    /**
     * Persentase pajak dari halaman Settings (kolom "Pajak (%)"), mis. 11 atau 10.5.
     * Kosong atau tidak valid memakai DEFAULT_TAX_PERCENT; hasil dibatasi 0 sampai 100.
     */
    private function taxPercentage(): float
    {
        $value = Setting::query()->where('key', 'tax_percentage')->value('value');

        if ($value === null || ! is_numeric(trim((string) $value))) {
            return self::DEFAULT_TAX_PERCENT;
        }

        return max(0.0, min(100.0, (float) $value));
    }

    /**
     * Daftar metode pembayaran yang diaktifkan admin, mis. ['cash', 'transfer'].
     * Pengaturan yang belum pernah disimpan dianggap aktif, supaya checkout
     * tidak tiba-tiba kosong sebelum admin membuka halaman Settings.
     *
     * @return list<string>
     */
    private function enabledPaymentMethods(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_values(self::PAYMENT_SETTINGS))
            ->pluck('value', 'key');

        $enabled = [];

        foreach (self::PAYMENT_SETTINGS as $method => $settingKey) {
            $value = $settings->get($settingKey);

            if ($value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                $enabled[] = $method;
            }
        }

        return $enabled;
    }

    /**
     * Ubah teks metode dari halaman checkout (mis. "Tunai (Cash)", "QRIS",
     * "Debit / Kredit - BCA") menjadi kunci: cash, qris, atau transfer.
     */
    private function resolvePaymentMethod(string $raw): ?string
    {
        $text = mb_strtolower($raw);

        foreach (self::PAYMENT_KEYWORDS as $method => $words) {
            foreach ($words as $word) {
                if (str_contains($text, $word)) {
                    return $method;
                }
            }
        }

        return null;
    }
}