@extends('layout.template')

@section('content')
    @php $isOwner = $isOwner ?? session('role') === 'staff'; @endphp

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

        @media (prefers-reduced-motion: reduce) {

            .bn-in,
            .bn-pop,
            .bn-avatar-wrap.is-new {
                animation: none;
                opacity: 1;
            }

            .bn-field,
            .bn-btn,
            .bn-avatar-wrap,
            .bn-meter-bar,
            .bn-eye,
            .bn-back span {
                transition: none;
            }

            .bn-eye.is-flip svg {
                animation: none;
            }
        }
    </style>

    <div class="w-full p-4 md:p-6 lg:p-8">

        <!-- Header -->
        <div class="bn-in flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6" style="--i:0">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Partner / User</h1>
                <p class="text-sm text-gray-500">Perbarui informasi akun partner atau Owner.</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
                class="bn-back self-start sm:self-auto px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full transition font-medium">
                <span>←</span> Kembali
            </a>
        </div>

        <form id="user-form" action="{{ route('admin.users.update', $user['id']) }}" method="post"
            enctype="multipart/form-data" class="w-full">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 w-full">

                <!-- Kolom Kiri: Foto Profil -->
                <div class="bn-in lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 flex flex-col items-center text-center h-fit"
                    style="--i:1">
                    <label class="block text-sm font-semibold text-gray-700 mb-4">Foto Profil</label>

                    <!-- Preview Foto Lama / Inisial -->
                    <div id="avatarWrap" class="bn-avatar-wrap">
                        <div id="photoPreviewContainer" class="bn-avatar-inner">
                            @if (!empty($user['photo']) && file_exists(public_path('uploads/users/' . $user['photo'])))
                                <img id="currentPhoto" src="{{ asset('uploads/users/' . $user['photo']) }}"
                                    alt="Foto Profil" class="w-full h-full object-cover">
                            @else
                                <div id="currentPhoto"
                                    class="w-full h-full bg-amber-100 text-[#754a30] font-bold text-4xl flex items-center justify-center">
                                    <span id="initialLetter">{{ strtoupper(substr($user['username'] ?? 'A', 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <p id="photoBadge" class="mt-4 text-xs font-medium text-gray-500">Foto saat ini</p>
                    <p id="photoName" class="text-xs text-gray-400 truncate max-w-full"></p>
                    <p id="photoError" class="hidden mt-1 text-xs text-red-500 font-medium"></p>

                    <div class="mt-4 w-full">
                        <input type="file" name="photo" id="photoInput" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-[#754a30] hover:file:bg-amber-100 transition cursor-pointer">
                        <p class="text-xs text-gray-400 mt-2">Format: JPG, JPEG, PNG (Maks. 2MB). Kosongkan jika tidak ingin
                            mengubah foto.</p>
                    </div>

                    <button type="button" id="resetPhoto"
                        class="hidden mt-3 px-4 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        Batalkan foto baru
                    </button>
                </div>

                <!-- Kolom Kanan: Data Akun -->
                <div class="bn-in lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-5"
                    style="--i:2">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                            <input type="text" id="username" name="username" value="{{ $user['username'] }}" required
                                class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ $user['email'] }}" required
                                class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        </div>
                    </div>

                    <!-- Input Password Baru dengan Tombol Mata (Eye Toggle) -->
                    <div>
                        <label for="passwordInput" class="block text-sm font-semibold text-gray-700 mb-2">Password Baru
                            <span class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah
                                password)</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="passwordInput" autocomplete="new-password"
                                class="bn-field w-full px-4 py-2.5 pr-12 border border-gray-200 rounded-xl focus:outline-none">

                            <button type="button" id="togglePassword" aria-label="Tampilkan password"
                                class="bn-eye absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-[#754a30] focus:outline-none">
                                <!-- Icon Mata Tertutup (Default) -->
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <!-- Indikator kekuatan password (muncul saat mengetik) -->
                        <div id="pwMeter" class="mt-2 h-5 opacity-0 transition-opacity duration-300">
                            <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div id="pwBar" class="bn-meter-bar h-full w-0 rounded-full bg-rose-500"></div>
                            </div>
                            <p id="pwLabel" class="mt-1 text-xs text-gray-500"></p>
                        </div>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Role / Level</label>
                        <select id="role" name="role"
                            class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none bg-white">
                            <option value="admin" {{ ($user['role'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                            @if ($isOwner)
                                <option value="staff" {{ ($user['role'] ?? '') === 'staff' ? 'selected' : '' }}>Owner
                                </option>
                            @endif
                        </select>
                    </div>

                    <hr class="border-gray-100">

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <a href="{{ route('admin.users.index') }}"
                            class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition text-center">
                            Batal
                        </a>
                        <button type="submit" id="submit-btn"
                            class="bn-btn px-6 py-2.5 bg-[#754a30] text-white text-sm font-semibold rounded-xl hover:bg-[#21140e] shadow-md inline-flex items-center justify-center gap-2">
                            <span id="submit-spinner" class="bn-spinner hidden"></span>
                            <span id="submit-label">Perbarui User</span>
                        </button>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <!-- Script: Toggle Show/Hide Password, Live Preview Foto, Kekuatan Password -->
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
            const badge = document.getElementById('photoBadge');
            const photoName = document.getElementById('photoName');
            const photoError = document.getElementById('photoError');
            const resetPhoto = document.getElementById('resetPhoto');
            const originalHTML = container.innerHTML;

            function showError(msg) {
                photoError.textContent = msg;
                photoError.classList.remove('hidden');
            }

            function restoreOriginal() {
                photoInput.value = '';
                container.innerHTML = originalHTML;
                badge.textContent = 'Foto saat ini';
                badge.classList.remove('text-[#754a30]');
                photoName.textContent = '';
                photoError.classList.add('hidden');
                resetPhoto.classList.add('hidden');
                bindInitial();
                avatarWrap.classList.remove('is-new');
                void avatarWrap.offsetWidth;
                avatarWrap.classList.add('is-new');
            }

            photoInput.addEventListener('change', function(event) {
                photoError.classList.add('hidden');
                const file = event.target.files[0];
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
                    container.innerHTML =
                        `<img src="${e.target.result}" alt="Preview" class="bn-pop w-full h-full object-cover">`;
                    badge.textContent = 'Foto baru (belum disimpan)';
                    badge.classList.add('text-[#754a30]');
                    photoName.textContent = file.name + ' · ' + (file.size / 1024).toFixed(0) + ' KB';
                    resetPhoto.classList.remove('hidden');

                    avatarWrap.classList.remove('is-new');
                    void avatarWrap.offsetWidth;
                    avatarWrap.classList.add('is-new');
                };
                reader.readAsDataURL(file);
            });

            resetPhoto.addEventListener('click', restoreOriginal);

            // ===== Inisial ikut berubah saat username diketik (jika belum ada foto) =====
            const usernameInput = document.getElementById('username');

            function bindInitial() {
                const letter = document.getElementById('initialLetter');
                if (!letter) return;
                usernameInput.oninput = function() {
                    letter.textContent = (usernameInput.value.trim().charAt(0) || 'A').toUpperCase();
                };
            }
            bindInitial();

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
