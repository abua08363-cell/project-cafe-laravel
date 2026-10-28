@extends('layout.template')

@section('content')
    @php
        // Collection Laravel -> array, supaya $category['name'] dan is_array() tetap jalan
        $categories = collect($categories ?? [])
            ->map(fn($c) => is_array($c) ? $c : $c->toArray())
            ->values()
            ->all();

        $totalCategories = count($categories);

        // Ikon otomatis berdasarkan kata kunci pada nama kategori
        if (!function_exists('bn_category_icon')) {
            function bn_category_icon(string $name): string
            {
                $n = strtolower($name);
                $map = [
                    'non' => '🧋',
                    'coffee' => '☕',
                    'kopi' => '☕',
                    'tea' => '🍵',
                    'teh' => '🍵',
                    'pastry' => '🥐',
                    'snack' => '🥨',
                    'dessert' => '🍰',
                    'cake' => '🍰',
                    'food' => '🍽️',
                    'makan' => '🍽️',
                ];
                foreach ($map as $key => $icon) {
                    if (strpos($n, $key) !== false) {
                        return $icon;
                    }
                }
                return '🏷️';
            }
        }
    @endphp

    <style>
        /* ===== Animasi masuk ===== */
        @keyframes bn-rise {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .bn-in {
            opacity: 0;
            animation: bn-rise .6s cubic-bezier(.22, 1, .36, 1) forwards;
            animation-delay: calc(var(--i, 0) * 80ms);
        }

        /* Baris tabel masuk satu per satu */
        @keyframes bn-row {
            from {
                opacity: 0;
                transform: translateX(-16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .bn-row {
            opacity: 0;
            animation: bn-row .5s cubic-bezier(.22, 1, .36, 1) forwards;
            animation-delay: calc(350ms + var(--r, 0) * 55ms);
            transition: background-color .25s, box-shadow .25s;
        }

        .bn-row:hover {
            background-color: #fbf6f2;
            box-shadow: inset 4px 0 0 #754a30;
        }

        .bn-row.is-hidden {
            display: none;
        }

        /* Ikon kategori */
        .bn-icon {
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
        }

        .bn-row:hover .bn-icon {
            transform: scale(1.18) rotate(-8deg);
            box-shadow: 0 6px 16px -6px rgba(117, 74, 48, .5);
        }

        /* Tombol utama */
        .bn-btn {
            position: relative;
            overflow: hidden;
            transition: transform .25s, box-shadow .25s, background-color .25s;
        }

        .bn-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .35) 50%, transparent 70%);
            transform: translateX(-120%);
        }

        .bn-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -10px rgba(117, 74, 48, .6);
        }

        .bn-btn:hover::after {
            transform: translateX(120%);
            transition: transform .7s ease;
        }

        .bn-btn:active {
            transform: translateY(0) scale(.97);
        }

        .bn-btn .bn-plus {
            display: inline-block;
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1);
        }

        .bn-btn:hover .bn-plus {
            transform: rotate(90deg) scale(1.2);
        }

        /* Tombol aksi di baris */
        .bn-act {
            transition: transform .2s, box-shadow .2s, background-color .2s;
        }

        .bn-act:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -8px rgba(0, 0, 0, .35);
        }

        .bn-act:active {
            transform: scale(.95);
        }

        /* Kartu statistik */
        .bn-stat {
            transition: transform .3s cubic-bezier(.22, 1, .36, 1), box-shadow .3s, border-color .3s;
        }

        .bn-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px -14px rgba(117, 74, 48, .45);
            border-color: #e3cdbd;
        }

        /* Search */
        .bn-search {
            transition: border-color .25s, box-shadow .25s;
        }

        .bn-search:focus {
            border-color: #754a30;
            box-shadow: 0 0 0 4px rgba(117, 74, 48, .15);
            outline: none;
        }

        /* Alert flash */
        @keyframes bn-alert-in {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bn-alert {
            animation: bn-alert-in .5s cubic-bezier(.34, 1.56, .64, 1) both;
            transition: opacity .4s, transform .4s, max-height .4s, margin .4s, padding .4s;
            max-height: 200px;
        }

        .bn-alert.is-closing {
            opacity: 0;
            transform: translateX(30px);
            max-height: 0;
            margin: 0;
            padding-top: 0;
            padding-bottom: 0;
            overflow: hidden;
        }

        /* Empty state */
        @keyframes bn-float {

            0%,
            100% {
                transform: translateY(0) rotate(0);
            }

            50% {
                transform: translateY(-8px) rotate(-4deg);
            }
        }

        .bn-float {
            display: inline-block;
            animation: bn-float 3s ease-in-out infinite;
        }

        /* Modal hapus */
        .bn-modal-backdrop {
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }

        .bn-modal-backdrop.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .bn-modal {
            transform: translateY(20px) scale(.92);
            opacity: 0;
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), opacity .25s;
        }

        .bn-modal-backdrop.is-open .bn-modal {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        @keyframes bn-shake {

            0%,
            100% {
                transform: rotate(0);
            }

            20% {
                transform: rotate(-12deg);
            }

            40% {
                transform: rotate(10deg);
            }

            60% {
                transform: rotate(-8deg);
            }

            80% {
                transform: rotate(6deg);
            }
        }

        .bn-modal-backdrop.is-open .bn-shake {
            animation: bn-shake .7s .25s ease;
        }

        @media (prefers-reduced-motion: reduce) {

            .bn-in,
            .bn-row,
            .bn-alert {
                animation: none;
                opacity: 1;
            }

            .bn-float,
            .bn-modal-backdrop.is-open .bn-shake {
                animation: none;
            }

            .bn-row,
            .bn-icon,
            .bn-btn,
            .bn-act,
            .bn-stat,
            .bn-modal,
            .bn-modal-backdrop {
                transition: none;
            }
        }
    </style>

    <div class="w-full">

        <!-- Header & Tombol Tambah -->
        <div class="bn-in mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" style="--i:0">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 md:text-3xl">Daftar Kategori Menu</h1>
                <p class="text-sm text-gray-500">Kelola kategori produk untuk menu BrewNest Coffee.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}"
                class="bn-btn inline-flex items-center gap-2 self-start rounded-xl bg-[#754a30] px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-[#21140e] sm:self-auto">
                <span class="bn-plus">➕</span> Tambah Kategori
            </a>
        </div>

        <!-- Alert Notifikasi -->
        @if (session('success'))
            <div
                class="bn-alert js-alert mb-4 flex items-start justify-between gap-3 rounded-xl bg-green-100 p-4 text-sm text-green-700">
                <span>{{ session('success') }}</span>
                <button type="button" class="js-alert-close text-lg leading-none text-green-700/70 hover:text-green-900"
                    aria-label="Tutup">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div
                class="bn-alert js-alert mb-4 flex items-start justify-between gap-3 rounded-xl bg-red-100 p-4 text-sm text-red-700">
                <span>{{ session('error') }}</span>
                <button type="button" class="js-alert-close text-lg leading-none text-red-700/70 hover:text-red-900"
                    aria-label="Tutup">&times;</button>
            </div>
        @endif

        <!-- Kartu Ringkasan -->
        <div class="bn-in bn-stat mb-6 flex w-full items-center gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
            style="--i:1">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-xl text-[#754a30]">🗂️</div>
            <div>
                <p class="text-xs text-gray-500">Total Kategori</p>
                <p class="js-count text-2xl font-bold tabular-nums text-gray-800" data-to="{{ $totalCategories }}">0</p>
            </div>
        </div>

        <!-- Tabel Kategori -->
        <div class="bn-in w-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm" style="--i:2">

            <!-- Toolbar pencarian -->
            <div
                class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between md:px-6">
                <div class="relative w-full sm:max-w-xs">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                    </span>
                    <input id="category-search" type="search" placeholder="Cari nama kategori..."
                        class="bn-search w-full rounded-xl border border-gray-300 py-2 pl-9 pr-4 text-sm text-gray-800">
                </div>
                <p class="text-xs text-gray-500">Menampilkan <span id="visible-count"
                        class="font-semibold tabular-nums text-gray-700">{{ $totalCategories }}</span> dari
                    {{ $totalCategories }} kategori</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="w-20 px-6 py-4">#</th>
                            <th class="px-6 py-4">Nama Kategori</th>
                            <th class="w-48 px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse ($categories as $category)
                            <tr class="bn-row js-row" style="--r:{{ $loop->index }};"
                                data-search="{{ mb_strtolower($category['name']) }}">
                                <td class="px-6 py-4 font-semibold tabular-nums text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="bn-icon flex h-10 w-10 items-center justify-center rounded-xl border border-amber-200 bg-amber-100 text-lg">
                                            {{ bn_category_icon($category['name']) }}
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $category['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category['id']) }}"
                                            class="bn-act rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">
                                            Edit
                                        </a>
                                        <button type="button" data-name="{{ $category['name'] }}"
                                            data-url="{{ route('admin.categories.destroy', $category['id']) }}"
                                            class="js-delete bn-act rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-14 text-center text-stone-500">
                                    <span class="bn-float text-4xl">🗂️</span>
                                    <p class="mt-3 italic">Belum ada kategori menu yang ditambahkan.</p>
                                    <a href="{{ route('admin.categories.create') }}"
                                        class="mt-4 inline-block rounded-lg bg-[#754a30] px-4 py-2 text-xs font-semibold not-italic text-white transition hover:bg-[#21140e]">Tambah
                                        kategori pertama</a>
                                </td>
                            </tr>
                        @endforelse

                        <!-- Muncul saat pencarian tidak menemukan hasil -->
                        <tr id="no-result" class="hidden">
                            <td colspan="3" class="py-12 text-center text-stone-500">
                                <span class="bn-float text-3xl">🔍</span>
                                <p class="mt-3 italic">Tidak ada kategori yang cocok dengan pencarian kamu.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Konfirmasi Hapus (satu form saja; action diisi saat tombol Hapus diklik) -->
    <div id="delete-modal"
        class="bn-modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        aria-hidden="true">
        <div class="bn-modal w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl" role="dialog"
            aria-modal="true" aria-labelledby="delete-title">
            <div
                class="bn-shake mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-2xl text-rose-600">
                🗑️</div>
            <h2 id="delete-title" class="mt-4 text-lg font-bold text-gray-800">Hapus kategori ini?</h2>
            <p class="mt-1 text-sm text-gray-500">Kategori <strong id="delete-name" class="text-gray-800"></strong> akan
                dihapus dan tidak bisa dikembalikan.</p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="delete-cancel"
                    class="bn-act flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">Batal</button>
                <form id="delete-form" action="" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bn-act w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Ya,
                        hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // ===== Hitung naik pada kartu statistik =====
            document.querySelectorAll('.js-count').forEach(el => {
                const to = parseInt(el.dataset.to, 10) || 0;
                if (reduceMotion || to === 0) {
                    el.textContent = to;
                    return;
                }
                const duration = 900;
                const start = performance.now() + 250;

                function tick(now) {
                    const t = Math.min(Math.max((now - start) / duration, 0), 1);
                    const eased = 1 - Math.pow(1 - t, 3);
                    el.textContent = Math.round(to * eased);
                    if (t < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });

            // ===== Alert: tutup manual + otomatis hilang =====
            function closeAlert(el) {
                el.classList.add('is-closing');
                setTimeout(() => el.remove(), 450);
            }
            document.querySelectorAll('.js-alert').forEach(el => {
                el.querySelector('.js-alert-close').addEventListener('click', () => closeAlert(el));
                setTimeout(() => closeAlert(el), 6000);
            });

            // ===== Pencarian =====
            const search = document.getElementById('category-search');
            const rows = Array.from(document.querySelectorAll('.js-row'));
            const noResult = document.getElementById('no-result');
            const visibleCount = document.getElementById('visible-count');

            search.addEventListener('input', () => {
                const q = search.value.trim().toLowerCase();
                let shown = 0;
                rows.forEach(row => {
                    const match = row.dataset.search.includes(q);
                    row.classList.toggle('is-hidden', !match);
                    if (match) {
                        shown++;
                        row.style.animation = 'none';
                        void row.offsetWidth;
                        row.style.animation = '';
                        row.style.animationDelay = (shown * 30) + 'ms';
                    }
                });
                visibleCount.textContent = shown;
                noResult.classList.toggle('hidden', shown !== 0 || rows.length === 0);
            });

            // ===== Modal hapus =====
            const modal = document.getElementById('delete-modal');
            const nameEl = document.getElementById('delete-name');
            const deleteForm = document.getElementById('delete-form');
            const cancelBtn = document.getElementById('delete-cancel');

            function openModal(url, name) {
                nameEl.textContent = name;
                deleteForm.action = url;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                cancelBtn.focus();
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('.js-delete').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    openModal(btn.dataset.url, btn.dataset.name);
                });
            });
            cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeModal();
            });
        })();
    </script>
@endsection
