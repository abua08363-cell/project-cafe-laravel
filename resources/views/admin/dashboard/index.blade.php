@extends('layout.template')

@section('content')

    <?php
    /* ---------------------------------------------------------
   Persiapan data (semua dihitung dari variabel controller)
   --------------------------------------------------------- */
    $orders = !empty($recent_orders) && is_array($recent_orders) ? $recent_orders : [];
    
    // Kelompokkan status supaya warnanya konsisten
    $statusGroup = static function ($raw): string {
        $k = strtolower(trim((string) $raw));
        if (in_array($k, ['paid', 'lunas', 'selesai', 'completed', 'complete', 'success', 'settlement', 'done'], true)) {
            return 'success';
        }
        if (in_array($k, ['process', 'processing', 'diproses', 'proses', 'preparing', 'cooking'], true)) {
            return 'process';
        }
        if (in_array($k, ['cancel', 'cancelled', 'canceled', 'batal', 'dibatalkan', 'failed', 'expire', 'expired', 'deny'], true)) {
            return 'failed';
        }
        if (in_array($k, ['pending', 'menunggu', 'unpaid', 'waiting', ''], true)) {
            return 'pending';
        }
        return 'other';
    };
    
    $groups = [
        'success' => ['label' => 'Lunas / Selesai', 'color' => '#10b981', 'badge' => 'bg-emerald-100 text-emerald-700', 'count' => 0],
        'process' => ['label' => 'Diproses', 'color' => '#3b82f6', 'badge' => 'bg-blue-100 text-blue-700', 'count' => 0],
        'pending' => ['label' => 'Menunggu', 'color' => '#f59e0b', 'badge' => 'bg-amber-100 text-[#754a30]', 'count' => 0],
        'failed' => ['label' => 'Dibatalkan', 'color' => '#ef4444', 'badge' => 'bg-red-100 text-red-700', 'count' => 0],
        'other' => ['label' => 'Lainnya', 'color' => '#a8a29e', 'badge' => 'bg-stone-100 text-stone-600', 'count' => 0],
    ];
    
    $sumTotal = 0.0;
    foreach ($orders as $o) {
        $groups[$statusGroup($o['payment_status'] ?? ($o['status'] ?? 'pending'))]['count']++;
        $sumTotal += (float) ($o['total'] ?? 0);
    }
    $orderCount = count($orders);
    $avgTotal = $orderCount ? $sumTotal / $orderCount : 0;
    
    // Donat SVG (r = 40, keliling = 2 * M_PI * 40 ≈ 251.33)
    $C = 2 * M_PI * 40;
    $off = 0.0;
    $segments = [];
    $visible = count(array_filter($groups, static fn($g) => $g['count'] > 0));
    foreach ($groups as $key => $g) {
        if ($g['count'] === 0) {
            continue;
        }
        $len = ($g['count'] / $orderCount) * $C;
        $draw = $visible > 1 ? max($len - 3, 0.5) : $len;
        $segments[] = ['color' => $g['color'], 'len' => round($draw, 2), 'off' => round($off, 2)];
        $off += $len;
    }
    
    // Kartu statistik (pastikan variabel total didefinisikan di controller, atau default ke 0)
    $stats = [['url' => 'admin/menu', 'label' => 'Total Menu', 'value' => (int) ($total_menu ?? 0), 'icon' => '☕', 'accent' => '#b9854f', 'bg' => 'from-amber-100 to-amber-50'], ['url' => 'admin/users', 'label' => 'Total Users', 'value' => (int) ($total_users ?? 0), 'icon' => '👥', 'accent' => '#3b82f6', 'bg' => 'from-blue-100 to-blue-50'], ['url' => 'admin/categories', 'label' => 'Categories', 'value' => (int) ($total_category ?? 0), 'icon' => '📁', 'accent' => '#10b981', 'bg' => 'from-emerald-100 to-emerald-50'], ['url' => 'admin/orders', 'label' => 'Total Orders', 'value' => (int) ($total_orders ?? 0), 'icon' => '🛒', 'accent' => '#f97316', 'bg' => 'from-orange-100 to-orange-50']];
    ?>

    <script>
        document.documentElement.classList.add('bn-js');
    </script>

    <style>
        /* ================= BrewNest Dashboard ================= */

        /* Reveal saat masuk layar (hanya aktif jika JS jalan) */
        .bn-js .bn-reveal {
            opacity: 0;
        }

        .bn-js .bn-reveal.is-in {
            animation: bnRise .85s cubic-bezier(.2, .8, .2, 1) var(--d, 0s) both;
        }

        @keyframes bnRise {
            from {
                opacity: 0;
                translate: 0 28px;
                scale: .97;
            }

            to {
                opacity: 1;
                translate: 0 0;
                scale: 1;
            }
        }

        /* ---------- Hero ---------- */
        .bn-hero {
            background: linear-gradient(120deg, #1d110b 0%, #3a2114 45%, #754a30 100%);
            background-size: 200% 200%;
            animation: bnGrad 12s ease-in-out infinite alternate;
        }

        @keyframes bnGrad {
            to {
                background-position: 100% 100%;
            }
        }

        .bn-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .09) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(120deg, transparent 10%, #000 90%);
            -webkit-mask-image: linear-gradient(120deg, transparent 10%, #000 90%);
        }

        .bn-blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px);
            pointer-events: none;
            animation: bnFloat 9s ease-in-out infinite alternate;
        }

        @keyframes bnFloat {
            to {
                transform: translate(40px, -24px) scale(1.2);
            }
        }

        .bn-shimmer {
            background: linear-gradient(90deg, #d9a46a 0%, #fff3df 50%, #d9a46a 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: bnShimmer 4s linear infinite;
        }

        @keyframes bnShimmer {
            to {
                background-position: -200% 0;
            }
        }

        .bn-cup {
            animation: bnBob 4.5s ease-in-out infinite;
        }

        @keyframes bnBob {

            0%,
            100% {
                transform: translateY(0) rotate(-4deg);
            }

            50% {
                transform: translateY(-12px) rotate(4deg);
            }
        }

        .bn-steam i {
            position: absolute;
            bottom: 0;
            width: 6px;
            height: 34px;
            border-radius: 9px;
            background: rgba(217, 164, 106, .6);
            filter: blur(4px);
            opacity: 0;
            animation: bnSteam 3s ease-in infinite;
        }

        .bn-steam i:nth-child(1) {
            left: 6px;
        }

        .bn-steam i:nth-child(2) {
            left: 24px;
            animation-delay: 1s;
        }

        .bn-steam i:nth-child(3) {
            left: 42px;
            animation-delay: 2s;
        }

        @keyframes bnSteam {
            0% {
                opacity: 0;
                transform: translateY(0) scaleX(1);
            }

            30% {
                opacity: .8;
            }

            100% {
                opacity: 0;
                transform: translateY(-56px) scaleX(2);
            }
        }

        /* ---------- Kartu statistik ---------- */
        .bn-card {
            transition: transform .25s ease-out, box-shadow .35s ease, border-color .35s ease;
            will-change: transform;
        }

        .bn-card:hover {
            box-shadow: 0 22px 44px -20px rgba(29, 17, 11, .3);
            border-color: color-mix(in srgb, var(--accent) 40%, #f3f4f6);
        }

        .bn-spot {
            position: absolute;
            inset: 0;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s;
            background: radial-gradient(280px circle at var(--mx, 50%) var(--my, 50%), color-mix(in srgb, var(--accent) 22%, transparent), transparent 70%);
        }

        .bn-card:hover .bn-spot {
            opacity: 1;
        }

        .bn-line {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 0;
            background: var(--accent);
            transition: width .55s cubic-bezier(.2, .8, .2, 1);
        }

        .bn-card:hover .bn-line {
            width: 100%;
        }

        .bn-card:hover .bn-icon {
            animation: bnWiggle .7s ease;
        }

        @keyframes bnWiggle {

            0%,
            100% {
                transform: rotate(0) scale(1);
            }

            25% {
                transform: rotate(-12deg) scale(1.15);
            }

            60% {
                transform: rotate(10deg) scale(1.15);
            }
        }

        .bn-arrow {
            transition: transform .3s;
        }

        .bn-card:hover .bn-arrow,
        .bn-link:hover .bn-arrow {
            transform: translateX(5px);
        }

        /* ---------- Titik "live" ---------- */
        .bn-live {
            position: relative;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #10b981;
        }

        .bn-live::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: #10b981;
            animation: bnPing 1.8s ease-out infinite;
        }

        @keyframes bnPing {
            to {
                transform: scale(3.2);
                opacity: 0;
            }
        }

        /* ---------- Tabel ---------- */
        .bn-js .bn-row {
            opacity: 0;
        }

        .bn-js .is-in .bn-row {
            animation: bnSlide .6s cubic-bezier(.2, .8, .2, 1) calc(.35s + var(--i, 0) * .08s) both;
        }

        @keyframes bnSlide {
            from {
                opacity: 0;
                translate: -22px 0;
            }

            to {
                opacity: 1;
                translate: 0 0;
            }
        }

        .bn-row {
            transition: background-color .25s;
        }

        .bn-row:hover {
            background-color: #fbf8f3;
        }

        .bn-row td:first-child {
            transition: box-shadow .25s;
        }

        .bn-row:hover td:first-child {
            box-shadow: inset 3px 0 0 #b9854f;
        }

        .bn-avatar {
            transition: transform .3s cubic-bezier(.3, 1.6, .5, 1);
        }

        .bn-row:hover .bn-avatar {
            transform: scale(1.12) rotate(-6deg);
        }

        /* ---------- Donat & bar ---------- */
        .bn-seg {
            stroke-dasharray: 0 251.33;
            transition: stroke-dasharray 1.3s cubic-bezier(.3, .7, .2, 1) calc(.4s + var(--k, 0) * .15s);
        }

        .is-in .bn-seg {
            stroke-dasharray: var(--len) 251.33;
        }

        .bn-js .bn-fill {
            width: 0;
            transition: width 1.2s cubic-bezier(.3, .7, .2, 1) calc(.5s + var(--k, 0) * .12s);
        }

        .bn-js .is-in .bn-fill {
            width: var(--w);
        }

        .bn-empty-cup {
            animation: bnBob 3.5s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {

            .bn-hero,
            .bn-blob,
            .bn-shimmer,
            .bn-cup,
            .bn-steam i,
            .bn-live::after,
            .bn-empty-cup {
                animation: none !important;
            }

            .bn-js .bn-reveal,
            .bn-js .bn-row {
                opacity: 1;
                animation: none !important;
            }

            .bn-seg,
            .bn-js .bn-fill {
                transition: none !important;
            }

            .bn-seg {
                stroke-dasharray: var(--len) 251.33;
            }

            .bn-js .bn-fill {
                width: var(--w);
            }
        }
    </style>

    <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

        <!-- ================= Hero ================= -->
        <section
            class="bn-hero bn-reveal relative overflow-hidden rounded-3xl p-6 text-white shadow-xl shadow-[#1d110b]/20 sm:p-9">
            <span class="bn-blob -left-10 -top-16 h-56 w-56 bg-[#d9a46a]/40" aria-hidden="true"></span>
            <span class="bn-blob -bottom-20 right-24 h-64 w-64 bg-emerald-400/20" style="animation-delay:-4s"
                aria-hidden="true"></span>

            <!-- Cangkir melayang -->
            <div class="pointer-events-none absolute right-6 top-1/2 hidden -translate-y-1/2 sm:block" aria-hidden="true">
                <div class="relative">
                    <div class="bn-steam absolute -top-10 left-3 h-10 w-14"><i></i><i></i><i></i></div>
                    <div class="bn-cup select-none text-[96px] leading-none drop-shadow-2xl">☕</div>
                </div>
            </div>

            <div class="relative max-w-xl">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-wide text-[#f3d9b6] ring-1 ring-white/15 backdrop-blur">
                    <span class="bn-live"></span> Panel Admin
                </span>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight sm:text-4xl">
                    <span id="bnGreeting">Selamat datang</span>,<br>
                    <span class="bn-shimmer">Dashboard BrewNest</span>
                </h1>
                <p class="mt-3 max-w-md text-sm leading-relaxed text-stone-300">
                    Selamat datang kembali di panel admin BrewNest Coffee. Pantau menu, pengguna, dan pesanan dalam satu
                    tempat.
                </p>

                <div
                    class="mt-5 inline-flex items-center gap-3 rounded-2xl bg-black/20 px-4 py-2.5 ring-1 ring-white/10 backdrop-blur">
                    <span class="text-lg" aria-hidden="true">🗓️</span>
                    <div class="leading-tight">
                        <p id="bnDate" class="text-xs font-semibold text-stone-200">&nbsp;</p>
                        <p id="bnClock" class="font-mono text-sm font-bold tabular-nums text-[#f3d9b6]">--:--:--</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= Kartu statistik ================= -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $i => $s)
                <a href="{{ url($s['url']) }}"
                    class="bn-card bn-reveal group relative flex items-center justify-between overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                    style="--accent:{{ $s['accent'] }};--d:{{ round(0.15 + $i * 0.1, 2) }}s">
                    <span class="bn-spot"></span>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $s['label'] }}</p>
                        <h3 class="mt-1 text-3xl font-extrabold tabular-nums text-gray-800"
                            data-count="{{ $s['value'] }}">0</h3>
                        <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-bold"
                            style="color:{{ $s['accent'] }}">
                            Kelola <span class="bn-arrow">→</span>
                        </span>
                    </div>
                    <div
                        class="bn-icon relative grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br {{ $s['bg'] }} text-2xl shadow-inner">
                        {!! $s['icon'] !!}
                    </div>
                    <span class="bn-line"></span>
                </a>
            @endforeach
        </div>

        <!-- ================= Pesanan + ringkasan ================= -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Tabel pesanan -->
            <div class="bn-reveal rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2"
                style="--d:.2s">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-lg font-bold text-gray-800">Pesanan Masuk Terbaru</h2>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-600">
                                <span class="bn-live"></span> Live
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">Pantau transaksi pesanan yang baru dilakukan oleh pelanggan.</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}"
                        class="bn-link inline-flex items-center gap-1 rounded-full bg-[#fbf8f3] px-3.5 py-1.5 text-xs font-bold text-[#754a30] ring-1 ring-[#ece2d4] transition hover:bg-[#754a30] hover:text-white">
                        Lihat Semua Orders <span class="bn-arrow">→</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="rounded-tl-xl px-4 py-3">ID Order</th>
                                <th class="px-4 py-3">Nama Pemesan</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="rounded-tr-xl px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @if ($orders)
                                <?php $no = 1; ?>
                                @foreach ($orders as $order)
                                    @php
                                        $name = (string) ($order['customer_name'] ?? 'Pelanggan');
                                        $raw = $order['payment_status'] ?? ($order['status'] ?? 'Pending');
                                        $badge = $groups[$statusGroup($raw)]['badge'];
                                        $hue = (crc32($name) % 40) + 18;
                                    @endphp
                                    <tr class="bn-row" style="--i:{{ $no }}">
                                        <td class="px-4 py-3 font-semibold text-gray-900">#{{ $no++ }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="bn-avatar grid h-9 w-9 flex-none place-items-center rounded-full text-xs font-bold text-white shadow-sm"
                                                    style="background:linear-gradient(135deg,hsl({{ $hue }} 55% 52%),hsl({{ $hue + 8 }} 50% 34%))">
                                                    {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
                                                </span>
                                                <span class="font-medium">{{ $name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-block rounded-lg bg-[#fbf8f3] px-2.5 py-1 text-xs font-semibold text-stone-600 ring-1 ring-[#ece2d4]">
                                                {{ $order['table_number'] ?? 'Dine In' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 font-bold text-gray-900">Rp
                                            {{ number_format($order['total'] ?? 0, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                                                {{ ucfirst($raw) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="py-10 text-center">
                                        <div class="bn-empty-cup text-4xl" aria-hidden="true">☕</div>
                                        <p class="mt-2 text-xs italic text-stone-400">Belum ada transaksi pesanan masuk.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel ringkasan -->
            <div class="space-y-6">

                <!-- Donat status -->
                <div class="bn-reveal rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6" style="--d:.3s">
                    <h2 class="text-base font-bold text-gray-800">Status Pesanan</h2>
                    <p class="text-xs text-gray-500">Dari {{ $orderCount }} pesanan terbaru.</p>

                    <div class="mt-5 flex items-center gap-5">
                        <div class="relative h-32 w-32 flex-none">
                            <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90" aria-hidden="true">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f3efe8"
                                    stroke-width="12" />
                                @foreach ($segments as $k => $seg)
                                    <circle class="bn-seg" cx="50" cy="50" r="40" fill="none"
                                        stroke="{{ $seg['color'] }}" stroke-width="12"
                                        stroke-dashoffset="-{{ $seg['off'] }}"
                                        style="--len:{{ $seg['len'] }};--k:{{ $k }}" />
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 grid place-items-center text-center">
                                <div>
                                    <p class="text-2xl font-extrabold tabular-nums text-gray-800"
                                        data-count="{{ $orderCount }}">0</p>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Pesanan</p>
                                </div>
                            </div>
                        </div>

                        <ul class="min-w-0 flex-1 space-y-2.5">
                            <?php $k = 0; ?>
                            @foreach ($groups as $g)
                                @if ($g['count'] === 0)
                                    @continue
                                @endif
                                @php
                                    $pct = round(($g['count'] / ($orderCount ?: 1)) * 100);
                                @endphp
                                <li>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="flex items-center gap-2 truncate font-semibold text-gray-600">
                                            <span class="h-2.5 w-2.5 flex-none rounded-full"
                                                style="background:{{ $g['color'] }}"></span>
                                            {{ $g['label'] }}
                                        </span>
                                        <span class="font-bold tabular-nums text-gray-800">{{ $g['count'] }}</span>
                                    </div>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                        <div class="bn-fill h-full rounded-full"
                                            style="--w:{{ $pct }}%;--k:{{ $k++ }};background:{{ $g['color'] }}">
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            @if (!$orderCount)
                                <li class="text-xs italic text-stone-400">Belum ada data.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Nilai transaksi -->
                <div class="bn-reveal relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#754a30] to-[#1d110b] p-5 text-white shadow-lg shadow-[#1d110b]/20 sm:p-6"
                    style="--d:.4s">
                    <span class="bn-blob -right-8 -top-10 h-32 w-32 bg-[#d9a46a]/30" aria-hidden="true"></span>
                    <p class="relative text-[11px] font-semibold uppercase tracking-[2px] text-[#f3d9b6]">Nilai Pesanan
                        Terbaru</p>
                    <p class="relative mt-2 text-3xl font-extrabold tabular-nums">
                        <span class="text-lg font-bold text-[#d9a46a]">Rp</span>
                        <span data-count="{{ (int) round($sumTotal) }}">0</span>
                    </p>
                    <div class="relative mt-4 flex items-center justify-between border-t border-white/15 pt-3 text-xs">
                        <span class="text-stone-300">Rata-rata per pesanan</span>
                        <span class="font-bold tabular-nums">Rp <span
                                data-count="{{ (int) round($avgTotal) }}">0</span></span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        (function() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const fmt = n => Math.round(n).toLocaleString('id-ID');

            /* ---------- Sapaan, tanggal & jam ---------- */
            const greet = document.getElementById('bnGreeting');
            const dateEl = document.getElementById('bnDate');
            const clock = document.getElementById('bnClock');

            function tickClock() {
                const d = new Date();
                const h = d.getHours();
                greet.textContent = h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 19 ? 'Selamat sore' :
                    'Selamat malam';
                dateEl.textContent = d.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                clock.textContent = d.toLocaleTimeString('id-ID', {
                    hour12: false
                }).replace(/\./g, ':');
            }
            tickClock();
            setInterval(tickClock, 1000);

            /* ---------- Hitung naik angka ---------- */
            function countUp(el) {
                const target = Number(el.dataset.count) || 0;
                if (reduce || target === 0) {
                    el.textContent = fmt(target);
                    return;
                }
                const dur = Math.min(2000, 900 + Math.log10(target + 1) * 250);
                const t0 = performance.now();
                (function step(now) {
                    const p = Math.min((now - t0) / dur, 1);
                    const e = p === 1 ? 1 : 1 - Math.pow(2, -10 * p); // easeOutExpo
                    el.textContent = fmt(target * e);
                    if (p < 1) requestAnimationFrame(step);
                })(t0);
            }

            /* ---------- Reveal saat scroll ---------- */
            const items = document.querySelectorAll('.bn-reveal');

            function show(el) {
                el.classList.add('is-in');
                el.querySelectorAll('[data-count]').forEach(countUp);
                if (el.hasAttribute('data-count')) countUp(el);
            }

            if (reduce || !('IntersectionObserver' in window)) {
                items.forEach(show);
            } else {
                const io = new IntersectionObserver(entries => {
                    entries.forEach(en => {
                        if (!en.isIntersecting) return;
                        const d = parseFloat(getComputedStyle(en.target).getPropertyValue('--d')) || 0;
                        setTimeout(() => show(en.target), Math.max(d * 1000 - 300, 0));
                        io.unobserve(en.target);
                    });
                }, {
                    threshold: .12
                });
                items.forEach(el => io.observe(el));
            }

            /* ---------- Spotlight + tilt pada kartu ---------- */
            if (!reduce && window.matchMedia('(hover: hover)').matches) {
                document.querySelectorAll('.bn-card').forEach(card => {
                    card.addEventListener('mousemove', e => {
                        const r = card.getBoundingClientRect();
                        const x = e.clientX - r.left;
                        const y = e.clientY - r.top;
                        card.style.setProperty('--mx', x + 'px');
                        card.style.setProperty('--my', y + 'px');
                        const rx = ((y / r.height) - .5) * -7;
                        const ry = ((x / r.width) - .5) * 9;
                        card.style.transform =
                            `perspective(700px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-4px)`;
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = '';
                    });
                });
            }
        })();
    </script>

@endsection
