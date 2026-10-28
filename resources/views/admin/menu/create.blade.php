@extends('layout.template')

@section('content')

    <style>
        /* ===== Animasi masuk (satu urutan saat halaman dibuka) ===== */
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

        /* ===== Input: naik sedikit + glow saat fokus ===== */
        .bn-field {
            transition: border-color .25s, box-shadow .25s, transform .25s, background-color .25s;
        }

        .bn-field:hover {
            border-color: #c9a98f;
        }

        .bn-field:focus {
            border-color: #754a30;
            box-shadow: 0 0 0 4px rgba(117, 74, 48, .15);
            transform: translateY(-2px);
        }

        /* ===== Tombol kembali: panah bergeser ===== */
        .bn-back span {
            display: inline-block;
            transition: transform .25s;
        }

        .bn-back:hover span {
            transform: translateX(-5px);
        }

        /* ===== Dropzone upload ===== */
        .bn-drop {
            transition: transform .3s cubic-bezier(.22, 1, .36, 1), background-color .3s, border-color .3s, box-shadow .3s;
        }

        .bn-drop:hover,
        .bn-drop.is-over {
            border-color: #754a30;
            background-color: #fbf6f2;
            box-shadow: 0 10px 30px -12px rgba(117, 74, 48, .35);
        }

        .bn-drop.is-over {
            transform: scale(1.03);
        }

        .bn-drop svg {
            transition: transform .4s cubic-bezier(.34, 1.56, .64, 1), color .3s;
        }

        .bn-drop:hover svg,
        .bn-drop.is-over svg {
            transform: translateY(-6px) rotate(-6deg) scale(1.1);
            color: #754a30;
        }

        /* ===== Preview gambar ===== */
        @keyframes bn-pop {
            0% {
                opacity: 0;
                transform: scale(.85) rotate(-2deg);
            }

            100% {
                opacity: 1;
                transform: scale(1) rotate(0);
            }
        }

        .bn-pop {
            animation: bn-pop .5s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        /* ===== Tombol simpan: kilau + loading ===== */
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

        /* ===== Hormati preferensi reduce motion ===== */
        @media (prefers-reduced-motion: reduce) {

            .bn-in,
            .bn-pop {
                animation: none;
                opacity: 1;
            }

            .bn-field,
            .bn-drop,
            .bn-btn,
            .bn-back span,
            .bn-drop svg {
                transition: none;
            }
        }
    </style>

    <div class="w-full">

        <!-- Header Page -->
        <div class="bn-in mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" style="--i:0">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 md:text-3xl">Tambah Menu Baru</h1>
                <p class="text-sm text-gray-500">Isi formulir di bawah untuk menambahkan menu kopi/makanan ke katalog
                    BrewNest.</p>
            </div>
            <a href="{{ url('admin/menu') }}"
                class="bn-back self-start rounded-full bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-300 sm:self-auto">
                <span>←</span> Kembali
            </a>
        </div>

        <!-- Error validasi dari server -->
        @if ($errors->any())
            <div role="alert" class="bn-in mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                style="--i:1">
                <p class="mb-1 font-semibold">Periksa kembali isian kamu:</p>
                <ul class="list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bn-in w-full rounded-2xl border border-gray-100 bg-white p-5 shadow-sm md:p-8" style="--i:1">
            <form id="menu-form" action="{{ url('admin/menu') }}" method="POST" enctype="multipart/form-data"
                class="w-full space-y-6">
                @csrf

                <div class="grid w-full grid-cols-1 gap-8 lg:grid-cols-3">

                    <!-- Kolom Kiri: Input Data Utama (2 Kolom) -->
                    <div class="space-y-5 lg:col-span-2">
                        <div class="bn-in" style="--i:2">
                            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Menu <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                placeholder="Contoh: Espresso Macchiato" required
                                class="bn-field w-full rounded-xl border border-gray-300 px-4 py-2.5 text-gray-800 focus:outline-none @error('name') border-red-400 @enderror">
                            @error('name')
                                <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="bn-in" style="--i:3">
                                <label for="category_id" class="mb-1 block text-sm font-semibold text-gray-700">Kategori
                                    <span class="text-red-500">*</span></label>
                                <select id="category_id" name="category_id" required
                                    class="bn-field w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-gray-800 focus:outline-none @error('category_id') border-red-400 @enderror">
                                    <option value="" disabled @selected(!old('category_id'))>-- Pilih Kategori --
                                    </option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected((string) old('category_id') === (string) $cat->id)>{{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="bn-in" style="--i:4">
                                <label for="price" class="mb-1 block text-sm font-semibold text-gray-700">Harga (Rp)
                                    <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-4 font-medium text-gray-500">Rp</span>
                                    <input type="number" id="price" name="price" value="{{ old('price') }}"
                                        placeholder="25000" min="0" required
                                        class="bn-field w-full rounded-xl border border-gray-300 py-2.5 pl-12 pr-4 text-gray-800 focus:outline-none @error('price') border-red-400 @enderror">
                                </div>
                                <p id="price-hint"
                                    class="mt-1 h-4 text-xs font-medium text-[#754a30] opacity-0 transition-opacity duration-300">
                                </p>
                                @error('price')
                                    <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="bn-in" style="--i:5">
                            <div class="mb-1 flex items-end justify-between">
                                <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi
                                    Menu</label>
                                <span id="desc-count" class="text-xs tabular-nums text-gray-400">0 / 300</span>
                            </div>
                            <textarea id="description" name="description" rows="6" maxlength="300"
                                placeholder="Tulis deskripsi rinci rasa, cita rasa, atau bahan menu..."
                                class="bn-field w-full resize-y rounded-xl border border-gray-300 px-4 py-2.5 text-gray-800 focus:outline-none @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Kolom Kanan: Upload Foto -->
                    <div class="bn-in space-y-4" style="--i:3">
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Foto Menu</label>

                        <label for="image" id="dropzone"
                            class="bn-drop block cursor-pointer rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                            <div class="space-y-2">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="text-sm font-medium text-gray-700">Tarik foto ke sini atau klik untuk memilih</p>
                                <p class="text-xs text-gray-500">PNG, JPG, WEBP max 2MB</p>
                            </div>
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" class="hidden">

                        <p id="image-error" class="hidden text-xs font-medium text-red-500"></p>
                        @error('image')
                            <p class="text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror

                        <!-- Preview Gambar -->
                        <div id="preview-container" class="relative mt-3 hidden">
                            <img id="image-preview" src="#" alt="Preview"
                                class="mx-auto h-48 w-full rounded-xl border border-gray-200 object-cover shadow-sm">
                            <button type="button" id="remove-image" aria-label="Hapus foto"
                                class="absolute right-2 top-2 h-8 w-8 rounded-full bg-black/60 text-lg leading-none text-white transition duration-300 hover:rotate-90 hover:bg-red-500">
                                &times;
                            </button>
                            <p id="file-name" class="mt-2 truncate text-center text-xs text-gray-500"></p>
                        </div>
                    </div>

                </div>

                <hr class="my-6 border-gray-100">

                <!-- Tombol Aksi -->
                <div class="bn-in flex flex-col-reverse gap-3 sm:flex-row sm:justify-end" style="--i:6">
                    <a href="{{ url('admin/menu') }}"
                        class="rounded-full border border-gray-300 px-6 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                        Batal
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bn-btn inline-flex items-center justify-center gap-2 rounded-full bg-[#754a30] px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-[#21140e]">
                        <span id="submit-spinner" class="bn-spinner hidden"></span>
                        <span id="submit-label">Simpan Menu</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        (function() {
            const MAX_SIZE = 2 * 1024 * 1024; // 2MB

            const input = document.getElementById('image');
            const dropzone = document.getElementById('dropzone');
            const container = document.getElementById('preview-container');
            const preview = document.getElementById('image-preview');
            const fileName = document.getElementById('file-name');
            const errorBox = document.getElementById('image-error');
            const removeBtn = document.getElementById('remove-image');

            function showError(msg) {
                errorBox.textContent = msg;
                errorBox.classList.remove('hidden');
            }

            function clearImage() {
                input.value = '';
                container.classList.add('hidden');
                preview.src = '#';
            }

            function handleFile(file) {
                errorBox.classList.add('hidden');
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    clearImage();
                    return showError('File harus berupa gambar (PNG, JPG, atau WEBP).');
                }
                if (file.size > MAX_SIZE) {
                    clearImage();
                    return showError('Ukuran foto melebihi 2MB. Pilih foto yang lebih kecil.');
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    fileName.textContent = file.name + ' · ' + (file.size / 1024).toFixed(0) + ' KB';
                    container.classList.remove('hidden');
                    // restart animasi pop setiap ganti foto
                    container.classList.remove('bn-pop');
                    void container.offsetWidth;
                    container.classList.add('bn-pop');
                };
                reader.readAsDataURL(file);
            }

            input.addEventListener('change', e => handleFile(e.target.files[0]));
            removeBtn.addEventListener('click', clearImage);

            // Drag & drop
            ['dragenter', 'dragover'].forEach(evt =>
                dropzone.addEventListener(evt, e => {
                    e.preventDefault();
                    dropzone.classList.add('is-over');
                })
            );
            ['dragleave', 'drop'].forEach(evt =>
                dropzone.addEventListener(evt, e => {
                    e.preventDefault();
                    dropzone.classList.remove('is-over');
                })
            );
            dropzone.addEventListener('drop', e => {
                const file = e.dataTransfer.files[0];
                if (!file) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                handleFile(file);
            });

            // Format harga langsung (Rp 25.000)
            const price = document.getElementById('price');
            const priceHint = document.getElementById('price-hint');

            function updatePriceHint() {
                const v = parseInt(price.value, 10);
                if (isNaN(v)) {
                    priceHint.classList.add('opacity-0');
                    return;
                }
                priceHint.textContent = 'Rp ' + v.toLocaleString('id-ID');
                priceHint.classList.remove('opacity-0');
            }
            price.addEventListener('input', updatePriceHint);

            // Counter deskripsi
            const desc = document.getElementById('description');
            const count = document.getElementById('desc-count');

            function updateCount() {
                count.textContent = desc.value.length + ' / 300';
                count.classList.toggle('text-red-500', desc.value.length >= 290);
            }
            desc.addEventListener('input', updateCount);

            // Sinkronkan tampilan jika form terisi ulang (old input setelah validasi gagal)
            updatePriceHint();
            updateCount();

            // Loading state saat submit
            const form = document.getElementById('menu-form');
            form.addEventListener('submit', () => {
                if (!form.checkValidity()) return;
                const btn = document.getElementById('submit-btn');
                btn.classList.add('is-loading');
                document.getElementById('submit-spinner').classList.remove('hidden');
                document.getElementById('submit-label').textContent = 'Menyimpan...';
            });
        })();
    </script>

@endsection
