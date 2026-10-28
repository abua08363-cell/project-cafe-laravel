@extends('layout.template')

@section('content')
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

        /* ===== Input ===== */
        .bn-field {
            transition: border-color .25s, box-shadow .25s, transform .25s;
        }

        .bn-field:hover {
            border-color: #c9a98f;
        }

        .bn-field:focus {
            border-color: #754a30;
            box-shadow: 0 0 0 4px rgba(117, 74, 48, .15);
            transform: translateY(-2px);
        }

        /* ===== Tombol ===== */
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

        .bn-btn.is-loading {
            pointer-events: none;
            opacity: .85;
        }

        @keyframes bn-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .bn-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: bn-spin .7s linear infinite;
        }

        .bn-back span {
            display: inline-block;
            transition: transform .25s;
        }

        .bn-back:hover span {
            transform: translateX(-5px);
        }

        /* ===== Kartu preview ===== */
        .bn-preview-icon {
            width: 96px;
            height: 96px;
            border-radius: 28px;
            background: linear-gradient(135deg, #fde9d3, #f3d2b3);
            border: 2px solid #ecc9a6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
            transition: transform .4s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
        }

        .bn-preview-icon:hover {
            transform: scale(1.1) rotate(-6deg);
            box-shadow: 0 14px 30px -12px rgba(117, 74, 48, .6);
        }

        .bn-bounce {
            animation: bn-bounce .5s cubic-bezier(.34, 1.56, .64, 1);
        }

        @keyframes bn-bounce {
            0% {
                transform: scale(.7) rotate(-10deg);
            }

            100% {
                transform: scale(1) rotate(0);
            }
        }

        /* Teks preview berganti dengan efek halus */
        .bn-swap {
            transition: opacity .15s, transform .15s;
        }

        .bn-swap.is-out {
            opacity: 0;
            transform: translateY(6px);
        }

        /* Lencana "diubah" */
        .bn-badge {
            transition: opacity .3s, transform .3s cubic-bezier(.34, 1.56, .64, 1);
            opacity: 0;
            transform: scale(.7);
        }

        .bn-badge.is-on {
            opacity: 1;
            transform: scale(1);
        }

        @media (prefers-reduced-motion: reduce) {

            .bn-in,
            .bn-bounce {
                animation: none;
                opacity: 1;
            }

            .bn-field,
            .bn-btn,
            .bn-back span,
            .bn-preview-icon,
            .bn-swap,
            .bn-badge {
                transition: none;
            }
        }
    </style>

    <div class="w-full p-4 md:p-6 lg:p-8">

        <!-- Header -->
        <div class="bn-in flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6" style="--i:0">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Kategori</h1>
                <p class="text-sm text-gray-500">Perbarui nama kategori menu.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}"
                class="bn-back self-start sm:self-auto px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full transition font-medium">
                <span>←</span> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">

            <!-- Form -->
            <div class="bn-in lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8" style="--i:1">
                <form id="category-form" action="{{ route('admin.categories.update', $category['id']) }}" method="post"
                    class="w-full">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700">Nama Kategori</label>
                            <span id="name-count" class="text-xs text-gray-400 tabular-nums">0 / 50</span>
                        </div>
                        <input type="text" id="name" name="name" value="{{ $category['name'] }}"
                            data-original="{{ $category['name'] }}" maxlength="50" required autofocus
                            class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        <p class="mt-2 text-xs text-gray-400">Contoh: Coffee, Non-Coffee, Pastry &amp; Snack, Dessert.</p>
                    </div>

                    <hr class="border-gray-100 mb-6">

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-between sm:items-center gap-3">
                        <button type="button" id="reset-name"
                            class="hidden self-start sm:self-auto px-4 py-2 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            Kembalikan nama awal
                        </button>
                        <span id="reset-spacer" class="hidden sm:block"></span>

                        <div class="flex flex-col-reverse sm:flex-row gap-3">
                            <a href="{{ route('admin.categories.index') }}"
                                class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition text-center">
                                Batal
                            </a>
                            <button type="submit" id="submit-btn"
                                class="bn-btn px-6 py-2.5 bg-[#754a30] text-white text-sm font-semibold rounded-xl hover:bg-[#21140e] shadow-md inline-flex items-center justify-center gap-2">
                                <span id="submit-spinner" class="bn-spinner hidden"></span>
                                <span id="submit-label">Perbarui Kategori</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Preview langsung -->
            <div class="bn-in lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 flex flex-col items-center text-center h-fit"
                style="--i:2">
                <p class="text-sm font-semibold text-gray-700 mb-4">Pratinjau</p>

                <div id="preview-icon" class="bn-preview-icon">☕</div>

                <h2 id="preview-name" class="bn-swap mt-4 text-lg font-bold text-gray-800 break-words max-w-full"></h2>

                <span id="changed-badge"
                    class="bn-badge mt-3 px-3 py-1 rounded-full bg-amber-100 text-[#754a30] text-xs font-semibold">
                    Belum disimpan
                </span>

                <p class="mt-4 text-xs text-gray-400">Ikon menyesuaikan nama kategori secara otomatis.</p>
            </div>

        </div>
    </div>

    <script>
        (function() {
            const input = document.getElementById('name');
            const count = document.getElementById('name-count');
            const previewEl = document.getElementById('preview-name');
            const iconEl = document.getElementById('preview-icon');
            const badge = document.getElementById('changed-badge');
            const resetBtn = document.getElementById('reset-name');
            const original = input.dataset.original;
            const MAX = parseInt(input.getAttribute('maxlength'), 10);

            // Ikon otomatis berdasarkan kata kunci (sama dengan halaman daftar kategori)
            const ICONS = [
                ['non', '🧋'],
                ['coffee', '☕'],
                ['kopi', '☕'],
                ['tea', '🍵'],
                ['teh', '🍵'],
                ['pastry', '🥐'],
                ['snack', '🥨'],
                ['dessert', '🍰'],
                ['cake', '🍰'],
                ['food', '🍽️'],
                ['makan', '🍽️']
            ];

            function pickIcon(name) {
                const n = name.toLowerCase();
                for (const [key, icon] of ICONS) {
                    if (n.includes(key)) return icon;
                }
                return '🏷️';
            }

            let lastIcon = '';

            function render(animate) {
                const value = input.value.trim();

                // Counter
                count.textContent = input.value.length + ' / ' + MAX;
                count.classList.toggle('text-red-500', input.value.length >= MAX - 5);

                // Nama pratinjau
                const text = value || 'Nama kategori';
                if (animate && previewEl.textContent !== text) {
                    previewEl.classList.add('is-out');
                    setTimeout(() => {
                        previewEl.textContent = text;
                        previewEl.classList.remove('is-out');
                    }, 120);
                } else {
                    previewEl.textContent = text;
                }
                previewEl.classList.toggle('text-gray-400', !value);

                // Ikon pratinjau (animasi hanya saat ikon berganti)
                const icon = value ? pickIcon(value) : '🏷️';
                if (icon !== lastIcon) {
                    iconEl.textContent = icon;
                    if (animate) {
                        iconEl.classList.remove('bn-bounce');
                        void iconEl.offsetWidth;
                        iconEl.classList.add('bn-bounce');
                    }
                    lastIcon = icon;
                }

                // Lencana & tombol kembalikan
                const changed = input.value !== original;
                badge.classList.toggle('is-on', changed);
                resetBtn.classList.toggle('hidden', !changed);
            }

            input.addEventListener('input', () => render(true));
            resetBtn.addEventListener('click', () => {
                input.value = original;
                render(true);
                input.focus();
            });
            render(false);

            // Loading state saat submit
            const form = document.getElementById('category-form');
            form.addEventListener('submit', () => {
                if (!form.checkValidity()) return;
                document.getElementById('submit-btn').classList.add('is-loading');
                document.getElementById('submit-spinner').classList.remove('hidden');
                document.getElementById('submit-label').textContent = 'Menyimpan...';
            });
        })();
    </script>
@endsection
