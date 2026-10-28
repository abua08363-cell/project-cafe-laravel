@extends('layout.template')

@section('content')

    @php
        /* ---------------------------------------------------------
       Persiapan data (dihitung dari $menus yang dikirim controller)
       --------------------------------------------------------- */
        // Mendukung array maupun Eloquent model / stdClass
        $menus = collect($menus ?? [])
            ->map(function ($m) {
                $menu = is_array($m) ? $m : (method_exists($m, 'toArray') ? $m->toArray() : (array) $m);

                $category = $menu['category'] ?? null;

                if (is_array($category)) {
                    $menu['category'] = $category['name'] ?? '';
                } elseif (is_object($category)) {
                    $menu['category'] = $category->name ?? '';
                }

                $menu['title'] = $menu['title'] ?? $menu['name'] ?? '';

                if (!empty($menu['image'])) {
                    $menu['image'] = ltrim((string) $menu['image'], '/');

                    if (!str_starts_with($menu['image'], 'uploads/')) {
                        $menu['image'] = 'uploads/menus/'.$menu['image'];
                    }
                }

                return $menu;
            })
            ->values()
            ->all();
        $total = count($menus);

        $catOf = static function (array $m): string {
            $c = trim((string) ($m['category'] ?? ''));
            return $c !== '' ? $c : 'Umum';
        };

        // Warna badge kategori: konsisten per nama
        $catStyle = static function (string $c): string {
            $h = crc32($c) % 360;
            return "background:hsl($h 70% 92%);color:hsl($h 55% 26%)";
        };

        $catCounts = [];
        $sumPrice = 0.0;
        $maxMenu = null;
        foreach ($menus as $m) {
            $c = $catOf($m);
            $catCounts[$c] = ($catCounts[$c] ?? 0) + 1;
            $p = (float) ($m['price'] ?? 0);
            $sumPrice += $p;
            if ($maxMenu === null || $p > (float) $maxMenu['price']) {
                $maxMenu = $m;
            }
        }
        ksort($catCounts, SORT_NATURAL | SORT_FLAG_CASE);
        $avgPrice = $total ? $sumPrice / $total : 0;

        $statCards = [
            [
                'label' => 'Total Menu',
                'value' => $total,
                'prefix' => '',
                'icon' => '☕',
                'accent' => '#b9854f',
                'bg' => 'from-amber-100 to-amber-50',
                'sub' => 'menu terdaftar',
            ],
            [
                'label' => 'Kategori',
                'value' => count($catCounts),
                'prefix' => '',
                'icon' => '📁',
                'accent' => '#10b981',
                'bg' => 'from-emerald-100 to-emerald-50',
                'sub' => 'jenis kategori',
            ],
            [
                'label' => 'Rata-rata Harga',
                'value' => (int) round($avgPrice),
                'prefix' => 'Rp ',
                'icon' => '🏷️',
                'accent' => '#3b82f6',
                'bg' => 'from-blue-100 to-blue-50',
                'sub' => 'per menu',
            ],
            [
                'label' => 'Termahal',
                'value' => (int) round((float) ($maxMenu['price'] ?? 0)),
                'prefix' => 'Rp ',
                'icon' => '👑',
                'accent' => '#f97316',
                'bg' => 'from-orange-100 to-orange-50',
                'sub' => $maxMenu ? (string) ($maxMenu['title'] ?? '-') : '-',
            ],
        ];

        $flashOk = session('success') ?? session('message');
        $flashErr = session('error');

        $iconEdit =
            '<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
        $iconDel =
            '<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>';
    @endphp

    <script>
        document.documentElement.classList.add('bm-js');
    </script>

    <style>
        /* ================= Kelola Menu ================= */
        .bm-js .bm-reveal {
            opacity: 0;
        }

        .bm-js .bm-reveal.is-in {
            animation: bmRise .85s cubic-bezier(.2, .8, .2, 1) var(--d, 0s) both;
        }

        @keyframes bmRise {
            from {
                opacity: 0;
                translate: 0 26px;
                scale: .97;
            }

            to {
                opacity: 1;
                translate: 0 0;
                scale: 1;
            }
        }

        /* Banner */
        .bm-hero {
            background: linear-gradient(120deg, #1d110b 0%, #3a2114 45%, #754a30 100%);
            background-size: 200% 200%;
            animation: bmGrad 12s ease-in-out infinite alternate;
        }

        @keyframes bmGrad {
            to {
                background-position: 100% 100%;
            }
        }

        .bm-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .09) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(120deg, transparent 10%, #000 90%);
            -webkit-mask-image: linear-gradient(120deg, transparent 10%, #000 90%);
        }

        .bm-blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px);
            pointer-events: none;
            animation: bmFloat 9s ease-in-out infinite alternate;
        }

        @keyframes bmFloat {
            to {
                transform: translate(40px, -24px) scale(1.2);
            }
        }

        .bm-shimmer {
            background: linear-gradient(90deg, #d9a46a 0%, #fff3df 50%, #d9a46a 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: bmShimmer 4s linear infinite;
        }

        @keyframes bmShimmer {
            to {
                background-position: -200% 0;
            }
        }

        .bm-cup {
            animation: bmBob 4.5s ease-in-out infinite;
        }

        @keyframes bmBob {

            0%,
            100% {
                transform: translateY(0) rotate(-4deg);
            }

            50% {
                transform: translateY(-10px) rotate(4deg);
            }
        }

        /* Tombol tambah */
        .bm-add {
            position: relative;
            overflow: hidden;
        }

        .bm-add::after {
            content: '';
            position: absolute;
            top: 0;
            left: -80%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, .55), transparent);
            transform: skewX(-20deg);
            animation: bmShine 3.2s ease-in-out 1.5s infinite;
        }

        @keyframes bmShine {
            0% {
                left: -80%;
            }

            40%,
            100% {
                left: 140%;
            }
        }

        .bm-add .bm-plus {
            transition: transform .4s cubic-bezier(.3, 1.6, .5, 1);
        }

        .bm-add:hover .bm-plus {
            transform: rotate(90deg) scale(1.15);
        }

        /* Kartu statistik */
        .bm-stat {
            transition: transform .25s ease-out, box-shadow .35s, border-color .35s;
            will-change: transform;
        }

        .bm-stat:hover {
            box-shadow: 0 20px 40px -20px rgba(29, 17, 11, .3);
            border-color: color-mix(in srgb, var(--accent) 40%, #f5f5f4);
        }

        .bm-spot {
            position: absolute;
            inset: 0;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s;
            background: radial-gradient(260px circle at var(--mx, 50%) var(--my, 50%), color-mix(in srgb, var(--accent) 22%, transparent), transparent 70%);
        }

        .bm-stat:hover .bm-spot {
            opacity: 1;
        }

        .bm-line {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 0;
            background: var(--accent);
            transition: width .55s cubic-bezier(.2, .8, .2, 1);
        }

        .bm-stat:hover .bm-line {
            width: 100%;
        }

        .bm-stat:hover .bm-sicon {
            animation: bmWiggle .7s ease;
        }

        @keyframes bmWiggle {

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

        /* Toolbar */
        .bm-chip {
            border: 1px solid #e7e0d6;
            background: #fff;
            transition: background .2s, color .2s, border-color .2s, transform .2s;
        }

        .bm-chip:hover {
            border-color: #b9854f;
            transform: translateY(-1px);
        }

        .bm-chip[aria-pressed="true"] {
            background: #1d110b;
            border-color: #1d110b;
            color: #fff;
        }

        .bm-chip[aria-pressed="true"] .bm-n {
            background: rgba(255, 255, 255, .18);
            color: #fff;
        }

        .bm-chip:focus-visible,
        .bm-seg button:focus-visible,
        #bmSearch:focus-visible,
        #bmSort:focus-visible {
            outline: 2px solid #b9854f;
            outline-offset: 2px;
        }

        .bm-seg button {
            transition: background .2s, color .2s;
        }

        .bm-seg button[aria-pressed="true"] {
            background: #1d110b;
            color: #fff;
        }

        .bm-chips {
            scrollbar-width: none;
        }

        .bm-chips::-webkit-scrollbar {
            display: none;
        }

        /* Item (baris & kartu) */
        .bm-js .bm-item {
            opacity: 0;
        }

        .bm-js .bm-item.bm-in {
            animation: bmItem .55s cubic-bezier(.2, .8, .2, 1) calc(var(--i, 0) * 45ms) both;
        }

        @keyframes bmItem {
            from {
                opacity: 0;
                translate: 0 18px;
                scale: .97;
            }

            to {
                opacity: 1;
                translate: 0 0;
                scale: 1;
            }
        }

        .bm-row {
            transition: background-color .25s;
        }

        .bm-row:hover {
            background: #fbf8f3;
        }

        .bm-row td:first-child {
            transition: box-shadow .25s;
        }

        .bm-row:hover td:first-child {
            box-shadow: inset 3px 0 0 #b9854f;
        }

        /* Gambar */
        .bm-thumb {
            position: relative;
            overflow: hidden;
            background: linear-gradient(110deg, #f3efe8 30%, #fbf8f3 50%, #f3efe8 70%);
            background-size: 200% 100%;
            animation: bmSkel 1.6s linear infinite;
        }

        @keyframes bmSkel {
            to {
                background-position: -200% 0;
            }
        }

        .bm-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity .5s, transform .6s cubic-bezier(.2, .8, .2, 1);
        }

        .bm-img.is-loaded {
            opacity: 1;
        }

        .bm-row:hover .bm-img,
        .bm-gcard:hover .bm-img {
            transform: scale(1.12);
        }

        .bm-row:hover .bm-thumb {
            animation: none;
        }

        /* Kartu tampilan grid */
        .bm-gcard {
            transition: transform .3s cubic-bezier(.2, .8, .2, 1), box-shadow .35s, border-color .35s;
        }

        .bm-gcard:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 44px -22px rgba(29, 17, 11, .35);
            border-color: #d9a46a;
        }

        /* Tombol aksi */
        .bm-btn {
            transition: transform .2s, background-color .2s, box-shadow .2s;
        }

        .bm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -8px rgba(29, 17, 11, .45);
        }

        .bm-btn:active {
            transform: translateY(0) scale(.97);
        }

        /* Modal hapus */
        .bm-modal {
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }

        .bm-modal.open {
            opacity: 1;
            pointer-events: auto;
        }

        .bm-modal .bm-dialog {
            transform: translateY(24px) scale(.92);
            transition: transform .4s cubic-bezier(.3, 1.4, .5, 1);
        }

        .bm-modal.open .bm-dialog {
            transform: none;
        }

        .bm-modal.open .bm-shake {
            animation: bmShake .6s ease .25s;
        }

        @keyframes bmShake {

            0%,
            100% {
                transform: rotate(0);
            }

            20% {
                transform: rotate(-14deg);
            }

            40% {
                transform: rotate(12deg);
            }

            60% {
                transform: rotate(-8deg);
            }

            80% {
                transform: rotate(5deg);
            }
        }

        .bm-spin {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 9999px;
            animation: bmSpin .7s linear infinite;
        }

        @keyframes bmSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast */
        .bm-toast {
            transform: translateY(-20px) scale(.95);
            opacity: 0;
            transition: transform .5s cubic-bezier(.3, 1.4, .5, 1), opacity .3s;
        }

        .bm-toast.show {
            transform: none;
            opacity: 1;
        }

        .bm-empty-cup {
            animation: bmBob 3.5s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {

            .bm-hero,
            .bm-blob,
            .bm-shimmer,
            .bm-cup,
            .bm-add::after,
            .bm-thumb,
            .bm-empty-cup {
                animation: none !important;
            }

            .bm-js .bm-reveal,
            .bm-js .bm-item {
                opacity: 1;
                animation: none !important;
            }

            .bm-img {
                transition: none;
            }
        }
    </style>

    <div class="mx-auto max-w-7xl space-y-6">

        <!-- ================= Banner ================= -->
        <section
            class="bm-hero bm-reveal relative overflow-hidden rounded-3xl p-6 text-white shadow-xl shadow-[#1d110b]/20 sm:p-8">
            <span class="bm-blob -left-10 -top-16 h-52 w-52 bg-[#d9a46a]/40" aria-hidden="true"></span>
            <span class="bm-blob -bottom-20 right-32 h-60 w-60 bg-emerald-400/20" style="animation-delay:-4s"
                aria-hidden="true"></span>
            <div class="bm-cup pointer-events-none absolute right-8 top-1/2 hidden -translate-y-1/2 select-none text-[84px] leading-none drop-shadow-2xl md:block"
                aria-hidden="true">☕</div>

            <div class="relative flex flex-col justify-between gap-5 sm:flex-row sm:items-center md:pr-32">
                <div>
                    <span
                        class="inline-block rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-wide text-[#f3d9b6] ring-1 ring-white/15 backdrop-blur">Panel
                        Admin</span>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">
                        Kelola <span class="bm-shimmer">Menu</span>
                    </h1>
                    <p class="mt-2 text-sm text-stone-300">Daftar menu BrewNest Coffee</p>
                </div>

                <a href="{{ url('admin/menu/create') }}"
                    class="bm-add inline-flex items-center justify-center gap-2 rounded-xl bg-[#d9a46a] px-5 py-3 font-bold text-[#1d110b] shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-[#e6b57d]">
                    <span class="bm-plus inline-block text-lg leading-none">+</span> Tambah Menu
                </a>
            </div>
        </section>

        <!-- ================= Statistik ================= -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($statCards as $i => $s)
                <div class="bm-stat bm-reveal relative flex items-center justify-between gap-3 overflow-hidden rounded-2xl border border-stone-100 bg-white p-5 shadow-sm"
                    style="--accent:{{ $s['accent'] }};--d:{{ round(0.15 + $i * 0.1, 2) }}s">
                    <span class="bm-spot"></span>
                    <div class="relative min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">{{ $s['label'] }}</p>
                        <p class="mt-1 text-2xl font-extrabold tabular-nums text-stone-800 sm:text-[26px]">
                            {{ $s['prefix'] }}<span data-count="{{ $s['value'] }}">0</span>
                        </p>
                        <p class="mt-0.5 truncate text-[11px] text-stone-400" title="{{ $s['sub'] }}">
                            {{ $s['sub'] }}</p>
                    </div>
                    <div
                        class="bm-sicon relative grid h-12 w-12 flex-none place-items-center rounded-2xl bg-gradient-to-br {{ $s['bg'] }} text-xl shadow-inner">
                        {{ $s['icon'] }}</div>
                    <span class="bm-line"></span>
                </div>
            @endforeach
        </div>

        <!-- ================= Data menu ================= -->
        <div class="bm-reveal rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-6" style="--d:.2s">

            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-stone-800">Data Menu</h2>
                <span class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-900">
                    Total: <span id="bmTotalBadge">{{ $total }}</span> menu
                </span>
            </div>

            @if ($total)
                <!-- Toolbar -->
                <div class="mb-5 space-y-3">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="relative flex-1">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" />
                            </svg>
                            <input id="bmSearch" type="search" autocomplete="off"
                                placeholder="Cari nama, deskripsi, atau kategori…  (tekan /)"
                                class="w-full rounded-xl border border-stone-300 bg-white py-2.5 pl-10 pr-4 text-sm placeholder:text-stone-400 focus:border-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-700/20">
                        </div>

                        <div class="flex items-center gap-3">
                            <label for="bmSort" class="sr-only">Urutkan</label>
                            <select id="bmSort"
                                class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm font-medium text-stone-700">
                                <option value="default">Urutan awal</option>
                                <option value="name">Nama A–Z</option>
                                <option value="price-asc">Harga termurah</option>
                                <option value="price-desc">Harga termahal</option>
                            </select>

                            <div class="bm-seg flex overflow-hidden rounded-xl border border-stone-300 text-xs font-semibold"
                                role="group" aria-label="Tampilan">
                                <button type="button" data-view="table" aria-pressed="true"
                                    class="flex items-center gap-1.5 px-3.5 py-2.5">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                        <path d="M4 6h16M4 12h16M4 18h16" />
                                    </svg> Tabel
                                </button>
                                <button type="button" data-view="grid" aria-pressed="false"
                                    class="flex items-center gap-1.5 border-l border-stone-300 px-3.5 py-2.5">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                    </svg> Kartu
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bm-chips -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" id="bmChips" role="group"
                        aria-label="Filter kategori">
                        <button type="button"
                            class="bm-chip flex flex-none items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold"
                            data-cat="" aria-pressed="true">
                            Semua <span
                                class="bm-n rounded-full bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold text-stone-500">{{ $total }}</span>
                        </button>
                        @foreach ($catCounts as $c => $n)
                            <button type="button"
                                class="bm-chip flex flex-none items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold"
                                data-cat="{{ $c }}" aria-pressed="false">
                                {{ $c }} <span
                                    class="bm-n rounded-full bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold text-stone-500">{{ $n }}</span>
                            </button>
                        @endforeach
                    </div>

                    <p class="text-xs text-stone-400" aria-live="polite">Menampilkan <span id="bmShown"
                            class="font-bold text-stone-600">{{ $total }}</span> dari {{ $total }} menu</p>
                </div>

                <!-- ===== Tampilan tabel ===== -->
                <div id="bmTable" class="overflow-x-auto">
                    <table class="w-full min-w-[750px] text-left text-sm">
                        <thead class="bg-stone-100 text-stone-700">
                            <tr>
                                <th class="w-12 rounded-l-lg p-4 text-center">No.</th>
                                <th class="w-20 p-4 text-center">Foto</th>
                                <th class="p-4">Nama Menu</th>
                                <th class="p-4">Kategori</th>
                                <th class="p-4">Harga</th>
                                <th class="rounded-r-lg p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bmRows" class="divide-y divide-stone-100">
                            @foreach ($menus as $i => $menu)
                                @php
                                    $cat = $catOf($menu);
                                    $title = (string) ($menu['title'] ?? '');
                                    $desc = (string) ($menu['description'] ?? '');
                                    $price = (float) ($menu['price'] ?? 0);
                                @endphp
                                <tr class="bm-row bm-item" data-menu data-idx="{{ $i }}"
                                    data-cat="{{ $cat }}" data-price="{{ $price }}"
                                    data-title="{{ $title }}"
                                    data-search="{{ mb_strtolower($title . ' ' . $desc . ' ' . $cat) }}">
                                    <td class="bm-no p-4 text-center font-medium text-stone-600">{{ $i + 1 }}</td>

                                    <td class="p-4 text-center">
                                        <div
                                            class="bm-thumb mx-auto grid h-12 w-12 place-items-center rounded-xl border border-stone-200 shadow-sm">
                                            <span class="text-lg" aria-hidden="true">☕</span>
                                            @if (!empty($menu['image']))
                                                <img src="{{ asset($menu['image']) }}"
                                                    alt="{{ $title }}" loading="lazy" class="bm-img"
                                                    onload="this.classList.add('is-loaded');this.parentNode.style.animation='none'"
                                                    onerror="this.remove()">
                                            @endif
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <div class="font-semibold text-stone-800">{{ $title }}</div>
                                        <div class="mt-0.5 line-clamp-1 text-xs text-stone-500">
                                            {{ $desc !== '' ? $desc : 'Menu BrewNest Coffee' }}</div>
                                    </td>

                                    <td class="p-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold"
                                            style="{{ $catStyle($cat) }}">{{ $cat }}</span>
                                    </td>

                                    <td class="whitespace-nowrap p-4 font-semibold text-stone-800">Rp
                                        {{ number_format($price, 0, ',', '.') }}</td>

                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ url('admin/menu/' . $menu['id'] . '/edit') }}"
                                                class="bm-btn inline-flex items-center gap-1.5 rounded-lg bg-amber-800 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-900">
                                                {!! $iconEdit !!} Edit
                                            </a>
                                            <button type="button" data-del="{{ url('admin/menu/' . $menu['id']) }}"
                                                data-name="{{ $title }}"
                                                class="bm-btn inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                                {!! $iconDel !!} Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ===== Tampilan kartu ===== -->
                <div id="bmGrid" class="hidden grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($menus as $i => $menu)
                        @php
                            $cat = $catOf($menu);
                            $title = (string) ($menu['title'] ?? '');
                            $desc = (string) ($menu['description'] ?? '');
                            $price = (float) ($menu['price'] ?? 0);
                        @endphp
                        <article
                            class="bm-gcard bm-item flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white"
                            data-menu data-idx="{{ $i }}" data-cat="{{ $cat }}"
                            data-price="{{ $price }}" data-title="{{ $title }}"
                            data-search="{{ mb_strtolower($title . ' ' . $desc . ' ' . $cat) }}">
                            <div class="bm-thumb relative grid aspect-[4/3] place-items-center">
                                <span class="text-5xl opacity-60" aria-hidden="true">☕</span>
                                @if (!empty($menu['image']))
                                    <img src="{{ asset($menu['image']) }}" alt="{{ $title }}"
                                        loading="lazy" class="bm-img"
                                        onload="this.classList.add('is-loaded');this.parentNode.style.animation='none'"
                                        onerror="this.remove()">
                                @endif
                                <span
                                    class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold shadow-sm backdrop-blur"
                                    style="{{ $catStyle($cat) }}">{{ $cat }}</span>
                                <span
                                    class="absolute bottom-3 right-3 rounded-full bg-[#1d110b]/90 px-3 py-1 text-xs font-bold text-white shadow-lg backdrop-blur">Rp
                                    {{ number_format($price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="font-bold text-stone-800">{{ $title }}</h3>
                                <p class="mt-1 line-clamp-2 flex-1 text-xs leading-relaxed text-stone-500">
                                    {{ $desc !== '' ? $desc : 'Menu BrewNest Coffee' }}</p>
                                <div class="mt-4 flex gap-2">
                                    <a href="{{ url('admin/menu/' . $menu['id'] . '/edit') }}"
                                        class="bm-btn inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-amber-800 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-900">
                                        {!! $iconEdit !!} Edit
                                    </a>
                                    <button type="button" data-del="{{ url('admin/menu/' . $menu['id']) }}"
                                        data-name="{{ $title }}"
                                        class="bm-btn inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                        {!! $iconDel !!} Hapus
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Hasil pencarian kosong -->
                <div id="bmNoResult" class="hidden py-12 text-center">
                    <div class="bm-empty-cup text-4xl" aria-hidden="true">🔍</div>
                    <p class="mt-3 font-semibold text-stone-700">Menu tidak ditemukan</p>
                    <p class="mt-1 text-sm text-stone-500">Coba kata kunci lain atau pilih kategori "Semua".</p>
                    <button type="button" id="bmReset"
                        class="bm-btn mt-4 rounded-lg bg-stone-800 px-4 py-2 text-xs font-semibold text-white">Reset
                        filter</button>
                </div>
            @else
                <!-- Belum ada menu -->
                <div class="py-12 text-center">
                    <div class="bm-empty-cup text-5xl" aria-hidden="true">☕</div>
                    <p class="mt-3 font-semibold text-stone-700">Belum ada data menu</p>
                    <p class="mt-1 text-sm text-stone-500">Tambahkan menu kopi untuk ditampilkan di sini.</p>
                    <a href="{{ url('admin/menu/create') }}"
                        class="bm-btn mt-5 inline-flex rounded-lg bg-amber-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-amber-900">+
                        Tambah Menu Pertama</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Form hapus tunggal (action diisi saat konfirmasi) -->
    <form id="bmDeleteForm" method="post" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <!-- Modal konfirmasi hapus -->
    <div id="bmModal" class="bm-modal fixed inset-0 z-[80] grid place-items-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="bmModalTitle" aria-hidden="true">
        <div class="absolute inset-0 bg-[#1d110b]/60 backdrop-blur-sm" data-close></div>
        <div class="bm-dialog relative w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">
            <div class="bm-shake mx-auto grid h-16 w-16 place-items-center rounded-full bg-red-50 text-3xl ring-8 ring-red-50/60"
                aria-hidden="true">🗑️</div>
            <h3 id="bmModalTitle" class="mt-4 text-lg font-bold text-stone-800">Hapus menu ini?</h3>
            <p class="mt-1.5 text-sm text-stone-500">
                <span id="bmModalName" class="font-semibold text-stone-700"></span> akan dihapus dari daftar dan tidak
                bisa dikembalikan.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="bmCancel" data-close
                    class="bm-btn flex-1 rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 hover:border-stone-500">Batal</button>
                <button type="button" id="bmConfirm"
                    class="bm-btn flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                    <span id="bmConfirmText">Ya, hapus</span>
                </button>
            </div>
        </div>
    </div>

    @if ($flashOk || $flashErr)
        <div id="bmToast"
            class="bm-toast fixed right-4 top-4 z-[90] flex max-w-sm items-start gap-3 rounded-2xl px-4 py-3 text-sm font-semibold shadow-xl {{ $flashErr ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white' }}"
            role="status">
            <span aria-hidden="true">{{ $flashErr ? '⚠️' : '✅' }}</span>
            <span>{{ $flashErr ?: $flashOk }}</span>
        </div>
    @endif

    <script>
        (function() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const $ = id => document.getElementById(id);
            const fmt = n => Math.round(n).toLocaleString('id-ID');

            /* ---------- Hitung naik ---------- */
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
                    el.textContent = fmt(target * (p === 1 ? 1 : 1 - Math.pow(2, -10 * p)));
                    if (p < 1) requestAnimationFrame(step);
                })(t0);
            }

            /* ---------- Reveal saat scroll ---------- */
            const reveals = document.querySelectorAll('.bm-reveal');

            function show(el) {
                el.classList.add('is-in');
                el.querySelectorAll('[data-count]').forEach(countUp);
                if (window.__bmAnimate && el.querySelector('[data-menu]')) window.__bmAnimate();
            }
            if (reduce || !('IntersectionObserver' in window)) {
                reveals.forEach(show);
            } else {
                const io = new IntersectionObserver(es => es.forEach(en => {
                    if (!en.isIntersecting) return;
                    const d = parseFloat(getComputedStyle(en.target).getPropertyValue('--d')) || 0;
                    setTimeout(() => show(en.target), Math.max(d * 1000 - 300, 0));
                    io.unobserve(en.target);
                }), {
                    threshold: .1
                });
                reveals.forEach(el => io.observe(el));
            }

            /* ---------- Spotlight + tilt kartu statistik ---------- */
            if (!reduce && window.matchMedia('(hover: hover)').matches) {
                document.querySelectorAll('.bm-stat').forEach(c => {
                    c.addEventListener('mousemove', e => {
                        const r = c.getBoundingClientRect(),
                            x = e.clientX - r.left,
                            y = e.clientY - r.top;
                        c.style.setProperty('--mx', x + 'px');
                        c.style.setProperty('--my', y + 'px');
                        c.style.transform =
                            `perspective(700px) rotateX(${((y / r.height) - .5) * -7}deg) rotateY(${((x / r.width) - .5) * 9}deg) translateY(-4px)`;
                    });
                    c.addEventListener('mouseleave', () => c.style.transform = '');
                });
            }

            /* ---------- Toast ---------- */
            const toast = $('bmToast');
            if (toast) {
                setTimeout(() => toast.classList.add('show'), 200);
                setTimeout(() => toast.classList.remove('show'), 4800);
            }

            /* ===================================================
               Filter, cari, urutkan, ganti tampilan
               =================================================== */
            const rowsBox = $('bmRows');
            if (!rowsBox) {
                // Tidak ada menu: hanya modal tidak diperlukan
                return;
            }
            const gridBox = $('bmGrid');
            const tableBox = $('bmTable');
            const search = $('bmSearch');
            const sortSel = $('bmSort');
            const noRes = $('bmNoResult');
            const shown = $('bmShown');
            const chips = Array.from(document.querySelectorAll('#bmChips .bm-chip'));
            const segBtns = Array.from(document.querySelectorAll('.bm-seg button'));

            const state = {
                q: '',
                cat: '',
                sort: 'default',
                view: 'table'
            };

            const rowEls = Array.from(rowsBox.children);
            const cardEls = Array.from(gridBox.children);

            function sorted(list) {
                const a = list.slice();
                const by = {
                    'default': (x, y) => x.dataset.idx - y.dataset.idx,
                    'name': (x, y) => x.dataset.title.localeCompare(y.dataset.title, 'id'),
                    'price-asc': (x, y) => x.dataset.price - y.dataset.price,
                    'price-desc': (x, y) => y.dataset.price - x.dataset.price
                };
                return a.sort(by[state.sort]);
            }

            function matches(el) {
                return (!state.cat || el.dataset.cat === state.cat) &&
                    (!state.q || el.dataset.search.includes(state.q));
            }

            function animateItems() {
                const box = state.view === 'table' ? rowEls : cardEls;
                let i = 0;
                box.forEach(el => {
                    el.classList.remove('bm-in');
                    if (el.classList.contains('hidden')) return;
                    el.style.setProperty('--i', Math.min(i++, 14));
                });
                void document.body.offsetWidth; // paksa reflow supaya animasi mengulang
                box.forEach(el => {
                    if (!el.classList.contains('hidden')) el.classList.add('bm-in');
                });
            }

            function render(animate) {
                const order = sorted(rowEls).map(r => r.dataset.idx);
                let count = 0;

                [
                    [rowsBox, rowEls],
                    [gridBox, cardEls]
                ].forEach(([box, els]) => {
                    const byIdx = Object.fromEntries(els.map(e => [e.dataset.idx, e]));
                    order.forEach(idx => box.appendChild(byIdx[idx]));
                });

                let n = 0;
                order.forEach(idx => {
                    const r = rowEls.find(e => e.dataset.idx === idx);
                    const c = cardEls.find(e => e.dataset.idx === idx);
                    const ok = matches(r);
                    r.classList.toggle('hidden', !ok);
                    c.classList.toggle('hidden', !ok);
                    if (ok) {
                        r.querySelector('.bm-no').textContent = ++n;
                        count++;
                    }
                });

                shown.textContent = count;
                noRes.classList.toggle('hidden', count !== 0);
                tableBox.classList.toggle('hidden', state.view !== 'table' || count === 0);
                gridBox.classList.toggle('hidden', state.view !== 'grid' || count === 0);
                gridBox.classList.toggle('grid', state.view === 'grid' && count > 0);

                if (animate !== false) animateItems();
            }

            search.addEventListener('input', () => {
                state.q = search.value.trim().toLowerCase();
                render();
            });
            sortSel.addEventListener('change', () => {
                state.sort = sortSel.value;
                render();
            });
            chips.forEach(ch => ch.addEventListener('click', () => {
                state.cat = ch.dataset.cat;
                chips.forEach(x => x.setAttribute('aria-pressed', String(x === ch)));
                render();
            }));
            segBtns.forEach(b => b.addEventListener('click', () => {
                state.view = b.dataset.view;
                segBtns.forEach(x => x.setAttribute('aria-pressed', String(x === b)));
                try {
                    localStorage.setItem('brewNestMenuView', state.view);
                } catch (e) {}
                render();
            }));
            $('bmReset').addEventListener('click', () => {
                search.value = '';
                state.q = '';
                state.cat = '';
                state.sort = 'default';
                sortSel.value = 'default';
                chips.forEach((x, i) => x.setAttribute('aria-pressed', String(i === 0)));
                render();
            });

            // Tombol "/" memfokuskan pencarian
            document.addEventListener('keydown', e => {
                if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) {
                    e.preventDefault();
                    search.focus();
                }
            });

            // Tampilan awal: ingat pilihan terakhir; layar kecil default kartu
            let saved = null;
            try {
                saved = localStorage.getItem('brewNestMenuView');
            } catch (e) {}
            state.view = saved || (window.innerWidth < 640 ? 'grid' : 'table');
            segBtns.forEach(x => x.setAttribute('aria-pressed', String(x.dataset.view === state.view)));
            render(false);

            /* ===================================================
               Modal hapus
               =================================================== */
            const modal = $('bmModal');
            const form = $('bmDeleteForm');
            let lastFocus = null;

            function openModal(url, name) {
                lastFocus = document.activeElement;
                form.action = url;
                $('bmModalName').textContent = '“' + name + '”';
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                setTimeout(() => $('bmCancel').focus(), 50);
            }

            function closeModal() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                if (lastFocus) lastFocus.focus();
            }

            document.addEventListener('click', e => {
                const del = e.target.closest('[data-del]');
                if (del) {
                    openModal(del.dataset.del, del.dataset.name);
                    return;
                }
                if (e.target.closest('[data-close]')) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
            });
            $('bmConfirm').addEventListener('click', e => {
                const b = e.currentTarget;
                b.disabled = true;
                b.classList.add('opacity-80', 'cursor-wait');
                b.innerHTML = '<span class="bm-spin"></span><span>Menghapus…</span>';
                form.submit();
            });
        })();
    </script>

@endsection
