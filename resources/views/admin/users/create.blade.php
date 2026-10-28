@extends('layout.template')

@section('content')

    @php
        // Hanya Owner (role "staff") yang boleh membuat akun Owner
        $isOwner = $isOwner ?? session('role') === 'staff';
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

        /* ===== Avatar ===== */
        .bn-avatar-wrap {
            position: relative;
            width: 128px;
            height: 128px;
            border-radius: 9999px;
            padding: 4px;
            background: conic-gradient(from 0deg, #754a30, #e3b98f, #754a30);
            transition: transform .4s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
        }

        .bn-avatar-wrap:hover {
            transform: scale(1.06) rotate(-3deg);
            box-shadow: 0 14px 30px -12px rgba(117, 74, 48, .6);
        }

        .bn-avatar-wrap.is-new {
            animation: bn-spin-once .8s cubic-bezier(.22, 1, .36, 1);
        }

        @keyframes bn-spin-once {
            from {
                transform: rotate(-180deg) scale(.7);
                opacity: .3;
            }

            to {
                transform: rotate(0) scale(1);
                opacity: 1;
            }
        }

        .bn-avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 9999px;
            overflow: hidden;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes bn-pop {
            0% {
                opacity: 0;
                transform: scale(.8);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .bn-pop {
            animation: bn-pop .45s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        /* Dropzone foto */
        .bn-drop {
            transition: transform .3s cubic-bezier(.22, 1, .36, 1), background-color .3s, border-color .3s;
        }

        .bn-drop.is-over {
            transform: scale(1.04);
            border-color: #754a30;
            background-color: #fbf6f2;
        }

        /* ===== Kekuatan password ===== */
        .bn-meter-bar {
            transition: width .4s cubic-bezier(.22, 1, .36, 1), background-color .3s;
        }

        /* ===== Ikon mata ===== */
        .bn-eye {
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), color .2s;
        }

        .bn-eye:hover {
            transform: scale(1.2);
        }

        .bn-eye.is-flip svg {
            animation: bn-flip .35s ease;
        }

        @keyframes bn-flip {
            0% {
                transform: scaleY(1);
            }

            50% {
                transform: scaleY(.1);
            }

            100% {
                transform: scaleY(1);
            }
        }

        .bn-back span {
            display: inline-block;
            transition: transform .25s;
        }

        .bn-back:hover span {
            transform: translateX(-5px);
        }

        /* Emoji default sedikit bergoyang agar terasa hidup */
        @keyframes bn-wave {

            0%,
            100% {
                transform: rotate(0);
            }

            25% {
                transform: rotate(-8deg);
            }

            75% {
                transform: rotate(8deg);
            }
        }

        .bn-wave {
            display: inline-block;
            animation: bn-wave 3s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {

            .bn-in,
            .bn-pop,
            .bn-avatar-wrap.is-new,
            .bn-wave {
                animation: none;
                opacity: 1;
            }

            .bn-field,
            .bn-btn,
            .bn-avatar-wrap,
            .bn-meter-bar,
            .bn-eye,
            .bn-back span,
            .bn-drop {
                transition: none;
            }

            .bn-eye.is-flip svg {
                animation: none;
            }
        }
    </style>

    <div class="w-full">

        <!-- Header -->
        <div class="bn-in mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" style="--i:0">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 md:text-3xl">Tambah Partner / User Baru</h1>
                <p class="text-sm text-gray-500">Masukkan data akun untuk Owner atau admin baru.</p>
            </div>
            <a href="{{ url('admin/users') }}"
                class="bn-back self-start rounded-full bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-300 sm:self-auto">
                <span>←</span> Kembali
            </a>
        </div>

        <!-- Error validasi dari server -->
        @if ($errors->any())
            <div role="alert" class="bn-in mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                style="--i:0">
                <p class="mb-1 font-semibold">Periksa kembali isian kamu:</p>
                <ul class="list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- enctype="multipart/form-data" agar form bisa mengirim file foto -->
        <form id="user-form" action="{{ url('admin/users') }}" method="post" enctype="multipart/form-data" class="w-full">
            @csrf

            <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-3">

                <!-- Kolom Kiri: Foto Profil -->
                <div class="bn-in flex h-fit flex-col items-center rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-sm md:p-8 lg:col-span-1"
                    style="--i:1">
                    <label class="mb-4 block text-sm font-semibold text-gray-700">Foto Profil</label>

                    <!-- Live Preview -->
                    <div id="avatarWrap" class="bn-avatar-wrap">
                        <div id="photoPreviewContainer" class="bn-avatar-inner">
                            <div
                                class="flex h-full w-full items-center justify-center bg-amber-100 text-4xl font-bold text-[#754a30]">
                                <span id="avatarPlaceholder" class="bn-wave">👤</span>
                            </div>
                        </div>
                    </div>

                    <p id="photoBadge" class="mt-4 text-xs font-medium text-gray-500">Belum ada foto</p>
                    <p id="photoName" class="max-w-full truncate text-xs text-gray-400"></p>
                    <p id="photoError" class="mt-1 hidden text-xs font-medium text-red-500"></p>
                    @error('photo')
                        <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                    @enderror

                    <label for="photoInput" id="dropzone"
                        class="bn-drop mt-4 block w-full cursor-pointer rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 p-4 hover:border-[#754a30] hover:bg-amber-50/40">
                        <p class="text-sm font-medium text-gray-700">Tarik foto ke sini atau klik untuk memilih</p>
                        <p class="mt-1 text-xs text-gray-400">Format: JPG, JPEG, PNG (Maks. 2MB).</p>
                    </label>
                    <input type="file" name="photo" id="photoInput" accept="image/*" class="hidden">

                    <button type="button" id="resetPhoto"
                        class="mt-3 hidden rounded-full bg-gray-100 px-4 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200">
                        Hapus foto
                    </button>
                </div>

                <!-- Kolom Kanan: Data Akun -->
                <div class="bn-in space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8 lg:col-span-2"
                    style="--i:2">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="username" class="mb-2 block text-sm font-semibold text-gray-700">Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required
                                class="bn-field w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:outline-none @error('username') border-red-400 @enderror">
                            @error('username')
                                <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                class="bn-field w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:outline-none @error('email') border-red-400 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password dengan tombol mata -->
                    <div>
                        <label for="passwordInput" class="mb-2 block text-sm font-semibold text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="passwordInput" required minlength="6"
                                autocomplete="new-password"
                                class="bn-field w-full rounded-xl border border-gray-200 px-4 py-2.5 pr-12 focus:outline-none @error('password') border-red-400 @enderror">

                            <button type="button" id="togglePassword" aria-label="Tampilkan password"
                                class="bn-eye absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-[#754a30] focus:outline-none">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror

                        <!-- Indikator kekuatan password -->
                        <div id="pwMeter" class="mt-2 h-5 opacity-0 transition-opacity duration-300">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                <div id="pwBar" class="bn-meter-bar h-full w-0 rounded-full bg-rose-500"></div>
                            </div>
                            <p id="pwLabel" class="mt-1 text-xs text-gray-500"></p>
                        </div>
                    </div>

                    <div>
                        <label for="role" class="mb-2 block text-sm font-semibold text-gray-700">Role / Level</label>
                        <select id="role" name="role"
                            class="bn-field w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 focus:outline-none @error('role') border-red-400 @enderror">
                            <option value="admin" @selected(old('role', 'admin') === 'admin')>Admin</option>
                            @if ($isOwner)
                                <option value="staff" @selected(old('role') === 'staff')>Owner</option>
                            @endif
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <hr class="border-gray-100">

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ url('admin/users') }}"
                            class="rounded-xl bg-gray-100 px-6 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
                            Batal
                        </a>
                        <button type="submit" id="submit-btn"
                            class="bn-btn inline-flex items-center justify-center gap-2 rounded-xl bg-[#754a30] px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-[#21140e]">
                            <span id="submit-spinner" class="bn-spinner hidden"></span>
                            <span id="submit-label">Simpan User</span>
                        </button>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <!-- Script: Toggle Password, Live Preview Foto, Kekuatan Password -->
    <script>
        (function() {
            const MAX_SIZE = 2 * 1024 * 1024; // 2MB

            // ===== Toggle Password Visibility =====
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');

            const ICON_HIDDEN =
                `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>`;
            const ICON_SHOWN =
                `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.innerHTML = type === 'text' ? ICON_SHOWN : ICON_HIDDEN;
                togglePassword.setAttribute('aria-label', type === 'text' ? 'Sembunyikan password' :
                    'Tampilkan password');

                togglePassword.classList.remove('is-flip');
                void togglePassword.offsetWidth;
                togglePassword.classList.add('is-flip');
            });

            // ===== Indikator kekuatan password =====
            const pwMeter = document.getElementById('pwMeter');
            const pwBar = document.getElementById('pwBar');
            const pwLabel = document.getElementById('pwLabel');

            const LEVELS = [{
                    w: '20%',
                    c: '#f43f5e',
                    t: 'Lemah'
                },
                {
                    w: '45%',
                    c: '#f59e0b',
                    t: 'Cukup'
                },
                {
                    w: '75%',
                    c: '#84cc16',
                    t: 'Kuat'
                },
                {
                    w: '100%',
                    c: '#16a34a',
                    t: 'Sangat kuat'
                }
            ];

            passwordInput.addEventListener('input', function() {
                const v = passwordInput.value;
                if (!v) {
                    pwMeter.classList.add('opacity-0');
                    pwBar.style.width = '0';
                    return;
                }
                let score = 0;
                if (v.length >= 8) score++;
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
                if (/\d/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                const level = LEVELS[Math.max(score - 1, 0)];

                pwMeter.classList.remove('opacity-0');
                pwBar.style.width = level.w;
                pwBar.style.backgroundColor = level.c;
                pwLabel.textContent = 'Kekuatan password: ' + level.t;
            });

            // ===== Live Preview Foto =====
            const photoInput = document.getElementById('photoInput');
            const container = document.getElementById('photoPreviewContainer');
            const avatarWrap = document.getElementById('avatarWrap');
            const dropzone = document.getElementById('dropzone');
            const badge = document.getElementById('photoBadge');
            const photoName = document.getElementById('photoName');
            const photoError = document.getElementById('photoError');
            const resetPhoto = document.getElementById('resetPhoto');
            const usernameInput = document.getElementById('username');
            let hasPhoto = false;

            function placeholderHTML() {
                const letter = usernameInput.value.trim().charAt(0).toUpperCase();
                const inner = letter ?
                    `<span id="avatarPlaceholder">${letter}</span>` :
                    `<span id="avatarPlaceholder" class="bn-wave">👤</span>`;
                return `<div class="w-full h-full bg-amber-100 text-[#754a30] font-bold text-4xl flex items-center justify-center">${inner}</div>`;
            }

            function replay() {
                avatarWrap.classList.remove('is-new');
                void avatarWrap.offsetWidth;
                avatarWrap.classList.add('is-new');
            }

            function showError(msg) {
                photoError.textContent = msg;
                photoError.classList.remove('hidden');
            }

            function clearPhoto() {
                photoInput.value = '';
                hasPhoto = false;
                container.innerHTML = placeholderHTML();
                badge.textContent = 'Belum ada foto';
                badge.classList.remove('text-[#754a30]');
                photoName.textContent = '';
                photoError.classList.add('hidden');
                resetPhoto.classList.add('hidden');
                replay();
            }

            function handleFile(file) {
                photoError.classList.add('hidden');
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    photoInput.value = '';
                    return showError('File harus berupa gambar.');
                }
                if (file.size > MAX_SIZE) {
                    photoInput.value = '';
                    return showError('Ukuran foto melebihi 2MB. Pilih foto yang lebih kecil.');
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    hasPhoto = true;
                    container.innerHTML =
                        `<img src="${e.target.result}" alt="Preview" class="bn-pop w-full h-full object-cover">`;
                    badge.textContent = 'Foto terpilih';
                    badge.classList.add('text-[#754a30]');
                    photoName.textContent = file.name + ' · ' + (file.size / 1024).toFixed(0) + ' KB';
                    resetPhoto.classList.remove('hidden');
                    replay();
                };
                reader.readAsDataURL(file);
            }

            photoInput.addEventListener('change', e => handleFile(e.target.files[0]));
            resetPhoto.addEventListener('click', clearPhoto);

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
                photoInput.files = dt.files;
                handleFile(file);
            });

            // Inisial menggantikan emoji saat username diketik (selama belum ada foto)
            usernameInput.addEventListener('input', function() {
                if (hasPhoto) return;
                const holder = document.getElementById('avatarPlaceholder');
                if (!holder) return;
                const letter = usernameInput.value.trim().charAt(0).toUpperCase();
                if (letter) {
                    holder.textContent = letter;
                    holder.classList.remove('bn-wave');
                } else {
                    holder.textContent = '👤';
                    holder.classList.add('bn-wave');
                }
            });
            // Jika form kembali dengan old('username'), tampilkan inisialnya langsung
            if (usernameInput.value.trim()) {
                container.innerHTML = placeholderHTML();
            }

            // ===== Loading state saat submit =====
            const form = document.getElementById('user-form');
            form.addEventListener('submit', function() {
                if (!form.checkValidity()) return;
                document.getElementById('submit-btn').classList.add('is-loading');
                document.getElementById('submit-spinner').classList.remove('hidden');
                document.getElementById('submit-label').textContent = 'Menyimpan...';
            });
        })();
    </script>

@endsection
