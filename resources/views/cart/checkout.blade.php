@php
    // Metode yang diaktifkan admin (dikirim CheckoutController@index).
    // Cadangan: kalau view dibuka tanpa variabel itu, tampilkan semua.
    $enabledMethods = $enabledMethods ?? ['cash', 'qris', 'transfer'];

    // Metode yang terpilih sejak awal = metode aktif pertama sesuai urutan tampilan
    $defaultMethod =
        collect(['cash', 'qris', 'transfer'])->first(fn($m) => in_array($m, $enabledMethods, true)) ?? 'cash';

    // Persentase pajak dari Settings (dikirim CheckoutController@index). Cadangan: 11.
    $taxPercentage = isset($taxPercentage) && is_numeric($taxPercentage) ? (float) $taxPercentage : 11.0;

    // Untuk tampilan: 11 => "11", 10.5 => "10,5"
    $taxLabel = rtrim(rtrim(number_format($taxPercentage, 2, ',', ''), '0'), ',');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#21140e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Metode Pembayaran - BrewNest Coffee' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('kopi.jpeg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            future: {
                hoverOnlyWhenSupported: true
            },
            theme: {
                extend: {
                    colors: {
                        coffee: '#21140e',
                        brown: '#754a30',
                        caramel: '#d7a66c',
                        cream: '#f8f0e3'
                    },
                    fontFamily: {
                        display: ['Fraunces', 'Georgia', 'serif'],
                        sans: ['DM Sans', 'system-ui', 'sans-serif']
                    }
                }
            }
        };
    </script>

    <style>
        /* ---------- Urutan muncul saat halaman dibuka ---------- */
        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rise {
            opacity: 0;
            animation: rise .65s cubic-bezier(.2, .7, .2, 1) forwards;
        }

        .d1 {
            animation-delay: .05s;
        }

        .d2 {
            animation-delay: .15s;
        }

        .d3 {
            animation-delay: .25s;
        }

        .d4 {
            animation-delay: .35s;
        }

        /* ---------- Kartu metode pembayaran ---------- */
        .pay-opt {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: pointer;
            padding: 14px 16px;
            background: #fff;
            border: 1px solid #e7e5e4;
            border-radius: 14px;
            transition: border-color .2s, background-color .2s, box-shadow .2s, transform .2s;
            -webkit-tap-highlight-color: transparent;
        }

        @media (hover: hover) {
            .pay-opt:hover {
                border-color: #d7a66c;
                box-shadow: 0 8px 20px -12px rgba(117, 74, 48, .45);
                transform: translateY(-1px);
            }
        }

        .pay-opt:has(input:checked) {
            border-color: #754a30;
            background: #fbf3e6;
            box-shadow: 0 10px 24px -14px rgba(117, 74, 48, .55);
        }

        .pay-opt:has(input:focus-visible) {
            outline: 2px solid #d7a66c;
            outline-offset: 2px;
        }

        .pay-ico {
            display: grid;
            place-items: center;
            flex: none;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #f8f0e3;
            font-size: 22px;
        }

        .bank-opt {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 12px;
            background: #fff;
            border: 1px solid #e7e5e4;
            border-radius: 10px;
            transition: border-color .2s, background-color .2s;
        }

        .bank-opt:has(input:checked) {
            border-color: #754a30;
            background: #fbf3e6;
        }

        .bank-opt:has(input:focus-visible) {
            outline: 2px solid #d7a66c;
            outline-offset: 2px;
        }

        /* ---------- Panel detail membuka halus ---------- */
        .collapse-panel {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows .45s cubic-bezier(.2, .7, .2, 1), opacity .35s ease;
        }

        .collapse-panel.open {
            grid-template-rows: 1fr;
            opacity: 1;
        }

        .collapse-panel>div {
            overflow: hidden;
            min-height: 0;
        }

        /* ---------- Garis scan di atas QR ---------- */
        @keyframes scan {
            0% {
                top: 6%;
                opacity: 0;
            }

            15%,
            85% {
                opacity: 1;
            }

            100% {
                top: 94%;
                opacity: 0;
            }
        }

        .scan-line {
            position: absolute;
            left: 8px;
            right: 8px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #d7a66c, transparent);
            box-shadow: 0 0 14px 3px rgba(215, 166, 108, .6);
            animation: scan 2.6s ease-in-out infinite;
        }

        /* ---------- Tanda pilihan ---------- */
        .radio-dot {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            border-radius: 9999px;
            border: 2px solid #d6d3d1;
            display: grid;
            place-items: center;
            transition: border-color .2s, background-color .2s;
        }

        .radio-dot::after {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #fff;
            transform: scale(0);
            transition: transform .25s cubic-bezier(.3, 1.6, .5, 1);
        }

        input:checked+.radio-dot {
            border-color: #754a30;
            background: #754a30;
        }

        input:checked+.radio-dot::after {
            transform: scale(1);
        }

        /* ---------- Item ringkasan masuk bergantian ---------- */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .item-in {
            opacity: 0;
            animation: slideIn .45s ease forwards;
        }

        /* ---------- Total berdenyut saat berubah ---------- */
        @keyframes pop {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.06);
            }
        }

        .pop {
            animation: pop .4s ease;
        }

        /* ---------- Tombol: warna "mengisi" saat hover ---------- */
        .btn-brew {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .btn-brew::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--fill, #f8f0e3);
            transform: translateY(101%);
            transition: transform .45s cubic-bezier(.2, .7, .2, 1);
            z-index: -1;
        }

        .btn-brew:focus-visible::before {
            transform: translateY(0);
        }

        @media (hover: hover) {
            .btn-brew:hover::before {
                transform: translateY(0);
            }
        }

        .btn-brew:active {
            transform: scale(.985);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(33, 20, 14, .25);
            border-top-color: #21140e;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* ---------- Toast ---------- */
        .toast {
            transform: translate(-50%, 24px);
            opacity: 0;
            pointer-events: none;
            transition: transform .4s cubic-bezier(.2, .7, .2, 1), opacity .3s ease;
        }

        .toast.show {
            transform: translate(-50%, 0);
            opacity: 1;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-6px);
            }

            40%,
            80% {
                transform: translateX(6px);
            }
        }

        .shake {
            animation: shake .45s ease-in-out;
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }

            .rise,
            .item-in {
                opacity: 1;
            }
        }
    </style>
</head>

<body
    class="min-h-screen bg-cream bg-[radial-gradient(50rem_26rem_at_100%_0%,rgba(215,166,108,.2),transparent_60%)] pb-28 font-sans text-coffee antialiased md:pb-0">

    <!-- Header -->
    <header
        class="sticky top-0 z-20 border-b border-caramel/30 bg-coffee px-4 py-3.5 text-white shadow-lg shadow-coffee/20 sm:px-6 sm:py-4">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3">
            <a href="{{ url('/') }}"
                class="flex min-w-0 items-center gap-2 font-display text-lg font-bold sm:text-xl">
                <span aria-hidden="true">☕</span>
                <span class="truncate">BrewNest<span class="hidden sm:inline"> Coffee &amp; Eatery</span></span>
            </a>
            <a href="{{ url('/') }}"
                class="group inline-flex flex-none items-center gap-1.5 rounded px-1 py-2 text-xs text-amber-200 transition hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel">
                <span class="transition-transform group-hover:-translate-x-1" aria-hidden="true">←</span> Lanjut
                belanja
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:py-10">

        <!-- Langkah checkout (keranjang → bayar → selesai) -->
        <ol class="rise d1 mb-6 flex items-center text-xs font-semibold sm:mb-8" aria-label="Langkah pemesanan">
            <li class="flex items-center gap-1.5 text-brown sm:gap-2">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-brown text-white"
                    aria-hidden="true">✓</span>
                Keranjang
            </li>
            <li class="mx-2 h-px flex-1 bg-brown/40 sm:mx-3" aria-hidden="true"></li>
            <li class="flex items-center gap-1.5 sm:gap-2" aria-current="step">
                <span
                    class="grid h-6 w-6 place-items-center rounded-full bg-coffee text-white ring-4 ring-caramel/40">2</span>
                Pembayaran
            </li>
            <li class="mx-2 h-px flex-1 bg-stone-300 sm:mx-3" aria-hidden="true"></li>
            <li class="flex items-center gap-1.5 text-stone-400 sm:gap-2">
                <span class="grid h-6 w-6 place-items-center rounded-full border border-stone-300"
                    aria-hidden="true">3</span>
                Selesai
            </li>
        </ol>

        <h1 class="rise d1 mb-6 font-display text-2xl font-bold sm:mb-8 sm:text-3xl">Informasi pembayaran</h1>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-8">

            <!-- Pilih Metode Pembayaran -->
            <div class="space-y-5 md:col-span-2">
                <div class="rise d2 space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-6">
                    <h2 class="text-sm font-bold text-stone-700">Pilih metode pembayaran</h2>

                    <!-- Tunai (aktif jika "Bayar di Kasir" dicentang di Settings) -->
                    @if (in_array('cash', $enabledMethods, true))
                        <label class="pay-opt">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="pay-ico" aria-hidden="true">💵</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold">Tunai (Cash)</p>
                                    <p class="text-xs leading-snug text-stone-500">Bayar langsung saat transaksi di
                                        kasir/meja.</p>
                                </div>
                            </div>
                            <input type="radio" name="payment_method" value="Tunai"
                                {{ $defaultMethod === 'cash' ? 'checked' : '' }} class="sr-only"
                                onclick="togglePaymentDetail('tunai')">
                            <span class="radio-dot"></span>
                        </label>
                    @endif

                    <!-- QRIS (aktif jika "QRIS / E-Wallet" dicentang di Settings) -->
                    @if (in_array('qris', $enabledMethods, true))
                        <label class="pay-opt">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="pay-ico" aria-hidden="true">📱</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold">QRIS</p>
                                    <p class="text-xs leading-snug text-stone-500">Scan QR untuk m-banking / e-wallet
                                        (GoPay, OVO, Dana, dll).</p>
                                </div>
                            </div>
                            <input type="radio" name="payment_method" value="QRIS"
                                {{ $defaultMethod === 'qris' ? 'checked' : '' }} class="sr-only"
                                onclick="togglePaymentDetail('qris')">
                            <span class="radio-dot"></span>
                        </label>

                        <!-- Area Detail QRIS -->
                        <div id="qrisDetail" class="collapse-panel">
                            <div>
                                <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50 p-4 text-center">
                                    <p class="text-xs font-bold text-stone-700">Scan QR code di bawah dengan aplikasi
                                        pembayaranmu:</p>
                                    <div
                                        class="relative inline-block max-w-full rounded-xl border bg-white p-2 shadow-sm">
                                        <img src="{{ asset('uploads/menus/qr.jpeg') }}" alt="QRIS Code"
                                            class="mx-auto h-52 w-52 max-w-full object-contain"
                                            onerror="this.onerror=null; this.classList.add('hidden'); document.getElementById('qrisFallback').classList.remove('hidden'); document.getElementById('qrisFallback').classList.add('flex'); document.getElementById('scanLine').classList.add('hidden');">
                                        <div id="qrisFallback"
                                            class="hidden h-52 w-52 max-w-full items-center justify-center text-xs italic text-stone-400">
                                            <span>QR belum tersedia.<br>Silakan minta kasir.</span>
                                        </div>
                                        <span id="scanLine" class="scan-line" aria-hidden="true"></span>
                                    </div>
                                    <p class="text-[11px] text-stone-500">Didukung: BCA, Mandiri, GoPay, OVO, DANA,
                                        ShopeePay</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Debit / Kredit (aktif jika "Transfer Bank" dicentang di Settings) -->
                    @if (in_array('transfer', $enabledMethods, true))
                        <label class="pay-opt">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="pay-ico" aria-hidden="true">💳</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold">Debit / Kredit</p>
                                    <p class="text-xs leading-snug text-stone-500">Gesek atau tap kartu di mesin EDC.
                                    </p>
                                </div>
                            </div>
                            <input type="radio" name="payment_method" value="Debit / Kredit"
                                {{ $defaultMethod === 'transfer' ? 'checked' : '' }} class="sr-only"
                                onclick="togglePaymentDetail('card')">
                            <span class="radio-dot"></span>
                        </label>

                        <!-- Area Pilihan Bank -->
                        <div id="cardDetail" class="collapse-panel">
                            <div>
                                <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50 p-4">
                                    <p class="text-xs font-bold text-stone-700">Pilih jenis kartu / bank:</p>
                                    <div id="bankOptions" class="grid grid-cols-2 gap-2 text-xs sm:grid-cols-3"></div>

                                    <div id="bankInfo"
                                        class="space-y-2 rounded-lg border border-stone-200 bg-white p-3 text-xs">
                                        <p id="bankInfoText" class="text-stone-600"></p>
                                        <a id="bankInfoLink" href="#" target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-block font-bold text-brown hover:underline"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rise d3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-6">
                    <label for="orderNotes" class="mb-2 block text-sm font-bold text-stone-700">Catatan pesanan
                        (opsional)</label>
                    <textarea id="orderNotes" rows="2" maxlength="500" placeholder="Contoh: tanpa gula, extra shot, es sedikit..."
                        class="w-full resize-none rounded-xl border border-stone-200 p-3 text-base outline-none transition placeholder:text-stone-400 focus:border-brown focus:ring-2 focus:ring-caramel/50 sm:text-sm"></textarea>
                </div>
            </div>

            <!-- Ringkasan -->
            <aside
                class="rise d4 h-fit space-y-4 rounded-2xl bg-coffee p-5 text-cream shadow-xl shadow-coffee/20 ring-1 ring-caramel/20 sm:p-6 md:sticky md:top-24">
                <h3 class="border-b border-white/10 pb-3 font-display text-lg font-bold">Ringkasan pesanan</h3>

                <div id="checkoutSummaryList" class="max-h-52 space-y-2.5 overflow-y-auto pr-1 text-sm"></div>

                <div class="space-y-1.5 border-t border-dashed border-white/20 pt-3 text-sm">
                    <div class="flex justify-between text-cream/70">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-cream/70">
                        <span>Pajak ({{ $taxLabel }}%)</span>
                        <span id="summaryTax">Rp 0</span>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-white/10 pt-3">
                        <span class="text-sm font-bold">Total pembayaran</span>
                        <span id="summaryTotal" class="origin-right font-display text-2xl font-bold text-caramel">Rp
                            0</span>
                    </div>
                </div>

                <!-- Tombol bayar versi desktop (di HP pakai bar bawah) -->
                <button data-pay-btn onclick="submitOrder()"
                    class="btn-brew hidden w-full items-center justify-center gap-2 rounded-xl bg-caramel py-3.5 text-sm font-bold text-coffee shadow-lg shadow-black/20 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-coffee disabled:cursor-not-allowed disabled:opacity-80 md:flex">
                    <span data-pay-label>Bayar sekarang</span>
                </button>
            </aside>
        </div>
    </main>

    <!-- Bar bayar khusus HP: total + tombol selalu terlihat -->
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-caramel/30 bg-coffee/95 px-4 pt-3 backdrop-blur md:hidden"
        style="padding-bottom: calc(.75rem + env(safe-area-inset-bottom, 0px));">
        <div class="mx-auto flex max-w-5xl items-center gap-4">
            <div class="min-w-0">
                <p class="text-[11px] text-cream/60">Total pembayaran</p>
                <p id="summaryTotalBar" class="origin-left font-display text-xl font-bold leading-tight text-caramel">
                    Rp 0</p>
            </div>
            <button data-pay-btn onclick="submitOrder()"
                class="btn-brew flex flex-1 items-center justify-center gap-2 rounded-xl bg-caramel py-3.5 text-sm font-bold text-coffee shadow-lg shadow-black/20 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cream focus-visible:ring-offset-2 focus-visible:ring-offset-coffee disabled:cursor-not-allowed disabled:opacity-80">
                <span data-pay-label>Bayar sekarang</span>
            </button>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" role="status" aria-live="polite"
        class="toast fixed bottom-28 left-1/2 z-50 max-w-[90%] rounded-xl bg-coffee px-5 py-3 text-sm text-white shadow-2xl ring-1 ring-caramel/30 md:bottom-6">
    </div>

    <script>
        // Tarif pajak dari halaman Settings (hanya untuk tampilan; server menghitung ulang saat pesanan dikirim)
        const TAX_RATE = @json($taxPercentage / 100);
        const cartData = JSON.parse(localStorage.getItem('brewNestCart')) || [];
        const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
        const escapeHtml = s => String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));

        // Hitung naik dari angka lama ke angka baru
        function countUp(el, to, duration = 800) {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce) {
                el.textContent = rupiah(to);
                return;
            }
            const start = performance.now();

            function frame(now) {
                const t = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - t, 3);
                el.textContent = rupiah(to * eased);
                if (t < 1) requestAnimationFrame(frame);
                else {
                    el.classList.remove('pop');
                    void el.offsetWidth;
                    el.classList.add('pop');
                }
            }
            requestAnimationFrame(frame);
        }

        function showToast(message) {
            const t = document.getElementById('toast');
            t.textContent = message;
            t.classList.add('show');
            clearTimeout(showToast.timer);
            showToast.timer = setTimeout(() => t.classList.remove('show'), 3200);
        }

        function renderCheckoutSummary() {
            const container = document.getElementById('checkoutSummaryList');
            let subtotal = 0;

            if (cartData.length === 0) {
                container.innerHTML = `
                    <div class="py-6 text-center">
                        <div class="mb-2 text-3xl">🫗</div>
                        <p class="text-cream/70">Keranjang masih kosong.</p>
                        <a href="{{ url('/') }}" class="mt-1 inline-block font-bold text-caramel hover:underline">Pilih menu dulu</a>
                    </div>`;
                return;
            }

            let html = '';
            cartData.forEach((item, i) => {
                const totalItem = item.price * item.qty;
                subtotal += totalItem;
                html += `
                    <div class="item-in flex justify-between gap-3" style="animation-delay:${0.45 + i * 0.07}s">
                        <span>${escapeHtml(item.name)} <span class="text-cream/50">×${item.qty}</span></span>
                        <span class="font-bold whitespace-nowrap">${rupiah(totalItem)}</span>
                    </div>`;
            });

            const tax = Math.round(subtotal * TAX_RATE);
            const grandTotal = subtotal + tax;

            container.innerHTML = html;
            document.getElementById('summarySubtotal').textContent = rupiah(subtotal);
            document.getElementById('summaryTax').textContent = rupiah(tax);
            countUp(document.getElementById('summaryTotal'), grandTotal);
            countUp(document.getElementById('summaryTotalBar'), grandTotal);
        }

        renderCheckoutSummary();

        // Data bank: tiap bank punya nama, info, dan link resmi masing-masing
        const banks = [{
                value: 'BCA Debit/Credit',
                label: 'BCA',
                url: 'https://www.bca.co.id',
                info: 'Tap atau gesek kartu BCA di mesin EDC BCA.'
            },
            {
                value: 'Mandiri Debit/Credit',
                label: 'Mandiri',
                url: 'https://bankmandiri.co.id',
                info: 'Tap atau gesek kartu Mandiri di mesin EDC Mandiri.'
            },
            {
                value: 'BRI EDC',
                label: 'BRI',
                url: 'https://bri.co.id',
                info: 'Tap atau gesek kartu BRI di mesin EDC BRI.'
            },
            {
                value: 'BNI EDC',
                label: 'BNI',
                url: 'https://www.bni.co.id',
                info: 'Tap atau gesek kartu BNI di mesin EDC BNI.'
            },
            {
                value: 'Visa',
                label: 'Visa',
                url: 'https://www.visa.co.id',
                info: 'Kartu berlogo Visa bisa digesek atau di-tap di mesin EDC.'
            },
            {
                value: 'Mastercard',
                label: 'Mastercard',
                url: 'https://www.mastercard.co.id',
                info: 'Kartu berlogo Mastercard bisa digesek atau di-tap di mesin EDC.'
            }
        ];

        function renderBankOptions() {
            const wrap = document.getElementById('bankOptions');
            // Panel bank tidak ada di halaman kalau metode Debit / Kredit dimatikan admin
            if (!wrap) return;

            wrap.innerHTML = banks.map((b, i) => `
                <label class="bank-opt">
                    <input type="radio" name="bank_option" value="${escapeHtml(b.value)}" class="sr-only"
                           ${i === 0 ? 'checked' : ''} onchange="showBankInfo(${i})">
                    <span class="radio-dot"></span>
                    <span class="font-semibold">${b.label}</span>
                </label>
            `).join('');
            showBankInfo(0);
        }

        function showBankInfo(index) {
            const box = document.getElementById('bankInfo');
            if (!box) return;

            const b = banks[index];
            document.getElementById('bankInfoText').textContent = b.info;
            const link = document.getElementById('bankInfoLink');
            link.href = b.url;
            link.textContent = 'Kunjungi situs ' + b.label + ' ↗';
            box.classList.remove('item-in');
            void box.offsetWidth;
            box.classList.add('item-in');
            box.style.animationDelay = '0s';
        }

        renderBankOptions();

        // Tampilkan detail pembayaran dinamis (QRIS / kartu) dengan animasi buka-tutup.
        // Panel yang tidak dirender (metode dimatikan admin) dilewati agar tidak error.
        function togglePaymentDetail(type) {
            const qris = document.getElementById('qrisDetail');
            const card = document.getElementById('cardDetail');
            if (qris) qris.classList.toggle('open', type === 'qris');
            if (card) card.classList.toggle('open', type === 'card');
        }

        // Saat halaman dibuka, sesuaikan panel dengan metode yang terpilih
        // (metode default bisa QRIS / Debit kalau Tunai dimatikan admin)
        (function syncInitialPayment() {
            const checked = document.querySelector('input[name="payment_method"]:checked');
            if (!checked) return;
            const map = {
                'Tunai': 'tunai',
                'QRIS': 'qris',
                'Debit / Kredit': 'card'
            };
            togglePaymentDetail(map[checked.value] || 'tunai');
        })();

        function submitOrder() {
            // Ada 2 tombol bayar (desktop & bar HP), keduanya dikendalikan bersamaan
            const buttons = document.querySelectorAll('[data-pay-btn]');
            const labels = document.querySelectorAll('[data-pay-label]');

            if (cartData.length === 0) {
                showToast('Keranjang belanja kosong!');
                buttons.forEach(b => {
                    b.classList.remove('shake');
                    void b.offsetWidth;
                    b.classList.add('shake');
                });
                return;
            }

            const paymentInput = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentInput) {
                showToast('Pilih metode pembayaran dulu.');
                return;
            }

            let selectedPayment = paymentInput.value;

            // Jika metode pembayaran kartu, tambahkan detail bank yang dipilih
            if (selectedPayment === 'Debit / Kredit') {
                const selectedBank = document.querySelector('input[name="bank_option"]:checked');
                if (selectedBank) {
                    selectedPayment += ' (' + selectedBank.value + ')';
                }
            }

            const customerName = localStorage.getItem('customerName') || 'Tamu';
            const tableNumber = localStorage.getItem('customerTable') || 'Takeaway';
            const notes = document.getElementById('orderNotes').value.trim();

            // Semua tombol masuk mode loading
            buttons.forEach(b => {
                b.disabled = true;
                b.insertAdjacentHTML('afterbegin', '<span class="spinner" aria-hidden="true"></span>');
            });
            labels.forEach(l => l.textContent = 'Memproses pesanan...');

            const resetButton = () => {
                buttons.forEach(b => {
                    b.disabled = false;
                    const sp = b.querySelector('.spinner');
                    if (sp) sp.remove();
                });
                labels.forEach(l => l.textContent = 'Bayar sekarang');
            };

            fetch('{{ url('checkout/process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        cart: cartData,
                        customer_name: customerName,
                        table_number: tableNumber,
                        payment_method: selectedPayment,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        localStorage.removeItem('brewNestCart');
                        labels.forEach(l => l.textContent = 'Pesanan diterima');
                        showToast('Pesanan diterima. Mengalihkan...');
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 700);
                    } else {
                        resetButton();
                        showToast('Gagal memproses pesanan: ' + (data.message || 'terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resetButton();
                    showToast('Koneksi bermasalah. Coba lagi.');
                });
        }

        // Tombol "kembali" di browser: pastikan tombol tidak macet di mode loading
        window.addEventListener('pageshow', e => {
            if (!e.persisted) return;
            document.querySelectorAll('[data-pay-btn]').forEach(b => {
                b.disabled = false;
                b.querySelector('.spinner')?.remove();
            });
            document.querySelectorAll('[data-pay-label]').forEach(l => l.textContent = 'Bayar sekarang');
        });
    </script>
</body>

</html>
