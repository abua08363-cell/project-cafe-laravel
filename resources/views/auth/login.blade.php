<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | BrewNest Coffee</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('kopi.jpeg') }}">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
        /* ---------- Panel kiri: latar bergerak pelan ---------- */
        @keyframes drift {
            from {
                transform: scale(1.05) translate(0, 0);
            }

            to {
                transform: scale(1.15) translate(-1.5%, -1%);
            }
        }

        .bg-drift {
            animation: drift 22s ease-in-out infinite alternate;
        }

        /* ---------- Momen utama: uap kopi naik ---------- */
        @keyframes steam {
            0% {
                stroke-dashoffset: 120;
                opacity: 0;
                transform: translateY(14px);
            }

            25% {
                opacity: .9;
            }

            70% {
                opacity: .5;
            }

            100% {
                stroke-dashoffset: 0;
                opacity: 0;
                transform: translateY(-22px);
            }
        }

        .steam {
            stroke-dasharray: 120;
            stroke-dashoffset: 120;
            animation: steam 4.2s ease-in-out infinite;
        }

        .steam:nth-child(2) {
            animation-delay: 1.3s;
            animation-duration: 4.8s;
        }

        .steam:nth-child(3) {
            animation-delay: 2.6s;
            animation-duration: 4.5s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .cup {
            animation: float 6s ease-in-out infinite;
        }

        /* ---------- Urutan muncul saat halaman dibuka ---------- */
        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rise {
            opacity: 0;
            animation: rise .7s cubic-bezier(.2, .7, .2, 1) forwards;
        }

        .d1 {
            animation-delay: .10s;
        }

        .d2 {
            animation-delay: .22s;
        }

        .d3 {
            animation-delay: .34s;
        }

        .d4 {
            animation-delay: .46s;
        }

        .d5 {
            animation-delay: .58s;
        }

        /* ---------- Field: garis aksen tumbuh saat fokus ---------- */
        .field {
            position: relative;
        }

        .field::after {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 0;
            height: 2px;
            background: linear-gradient(90deg, #d7a66c, #754a30);
            border-radius: 2px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s ease;
        }

        .field:focus-within::after {
            transform: scaleX(1);
        }

        /* ---------- Tombol: kopi "mengisi" saat hover ---------- */
        .btn-brew {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .btn-brew::before {
            content: "";
            position: absolute;
            inset: 0;
            background: #21140e;
            transform: translateY(101%);
            transition: transform .45s cubic-bezier(.2, .7, .2, 1);
            z-index: -1;
        }

        .btn-brew:hover::before,
        .btn-brew:focus-visible::before {
            transform: translateY(0);
        }

        .btn-brew:active {
            transform: scale(.985);
        }

        /* ---------- Spinner ---------- */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* ---------- Pesan error bergetar ---------- */
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

        /* ---------- Hormati preferensi "kurangi gerakan" ---------- */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }

            .rise {
                opacity: 1;
            }

            .steam {
                opacity: .6;
                stroke-dashoffset: 0;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-cream font-sans text-coffee antialiased">

    <main class="grid min-h-screen md:grid-cols-2">

        <!-- BAGIAN KIRI -->
        <section class="relative hidden items-center justify-center overflow-hidden bg-coffee p-12 text-white md:flex">

            <div class="bg-drift absolute inset-0 opacity-40"
                style="background-image: url('https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=1200&q=80'); background-size: cover; background-position: center;">
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-coffee/60 via-coffee/75 to-coffee"></div>

            <div class="relative z-10 max-w-md text-center">

                <!-- Cangkir dengan uap -->
                <svg class="cup mx-auto mb-8 h-40 w-40" viewBox="0 0 160 160" fill="none" aria-hidden="true">
                    <g stroke="#f8f0e3" stroke-width="3" stroke-linecap="round">
                        <path class="steam" d="M60 62c-9-10 9-17 0-28s9-17 0-26" />
                        <path class="steam" d="M80 62c-9-10 9-17 0-28s9-17 0-26" />
                        <path class="steam" d="M100 62c-9-10 9-17 0-28s9-17 0-26" />
                    </g>
                    <!-- gagang -->
                    <path d="M121 84h8a14 14 0 0 1 0 28h-12" stroke="#d7a66c" stroke-width="7" stroke-linecap="round" />
                    <!-- badan cangkir -->
                    <path d="M37 76h88v22a34 34 0 0 1-34 34H71a34 34 0 0 1-34-34V76z" fill="#d7a66c" />
                    <path d="M37 76h88v8H37z" fill="#754a30" />
                    <!-- piring -->
                    <ellipse cx="81" cy="142" rx="50" ry="6" fill="#754a30"
                        opacity=".7" />
                </svg>

                <h1 class="rise d1 mb-4 font-display text-5xl font-bold leading-tight">
                    BrewNest Coffee
                </h1>

                <p class="rise d2 mb-6 text-caramel">
                    Coffee, comfort, quality
                </p>

                <p class="rise d3 leading-8 text-stone-300">
                    Selamat datang kembali. Kelola menu, pantau pesanan, dan atur kedai kopi kamu dari satu dashboard.
                </p>
            </div>
        </section>

        <!-- BAGIAN LOGIN -->
        <section class="flex items-center justify-center px-6 py-12">

            <div class="w-full max-w-md">

                <a href="{{ url('/') }}"
                    class="rise d1 group mb-10 inline-flex items-center gap-2 text-sm text-brown transition hover:text-coffee focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel rounded">
                    <span class="transition-transform group-hover:-translate-x-1">←</span>
                    Kembali ke halaman utama
                </a>

                <div class="rise d1 mb-8 md:hidden">
                    <div class="mb-3 text-5xl">☕</div>
                    <h1 class="font-display text-3xl font-bold text-coffee">BrewNest Coffee</h1>
                </div>

                <div class="rise d2 mb-8">
                    <h2 class="mb-3 font-display text-4xl font-bold">
                        Masuk ke panel admin
                    </h2>
                    <p class="text-stone-600">
                        Gunakan akun admin untuk mengelola BrewNest Coffee.
                    </p>
                </div>

                @if (session('error'))
                    <div role="alert"
                        class="shake mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <span aria-hidden="true">⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.attempt') }}" method="post" id="loginForm" class="space-y-5">

                    @csrf

                    <div class="rise d3">
                        <label for="username" class="mb-2 block font-semibold">Username</label>
                        <div class="field">
                            <input type="text" name="username" id="username" placeholder="Masukkan username"
                                autocomplete="username" autofocus required
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition placeholder:text-stone-400 focus:border-brown focus:shadow-[0_8px_24px_-12px_rgba(117,74,48,.45)]">
                        </div>
                    </div>

                    <div class="rise d4">
                        <label for="password" class="mb-2 block font-semibold">Password</label>
                        <div class="field relative">
                            <input type="password" name="password" id="password" placeholder="Masukkan password"
                                autocomplete="current-password" required
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 pr-32 outline-none transition placeholder:text-stone-400 focus:border-brown focus:shadow-[0_8px_24px_-12px_rgba(117,74,48,.45)]">

                            <button type="button" id="togglePassword" aria-pressed="false"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-sm font-semibold text-brown transition hover:bg-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel">
                                Lihat
                            </button>
                        </div>
                        <p id="capsHint" class="mt-2 hidden text-sm text-amber-700">
                            Caps Lock sedang aktif.
                        </p>
                    </div>

                    <div class="rise d5">
                        <button type="submit" id="loginButton"
                            class="btn-brew flex w-full items-center justify-center gap-3 rounded-xl bg-brown py-3.5 font-bold text-white shadow-lg shadow-brown/25 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel focus-visible:ring-offset-2 focus-visible:ring-offset-cream disabled:cursor-not-allowed disabled:opacity-80">
                            <span id="loginLabel">Masuk ke dashboard</span>
                        </button>
                    </div>

                </form>

                <p class="rise d5 mt-8 text-center text-sm text-stone-500">
                    &copy; {{ date('Y') }} BrewNest Coffee
                </p>

            </div>
        </section>
    </main>

    <script>
        const form = document.getElementById('loginForm');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const capsHint = document.getElementById('capsHint');
        const loginButton = document.getElementById('loginButton');
        const loginLabel = document.getElementById('loginLabel');

        // Tampilkan atau sembunyikan password
        togglePassword.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'Sembunyikan' : 'Lihat';
            togglePassword.setAttribute('aria-pressed', String(isPassword));
        });

        // Peringatan Caps Lock
        function checkCaps(e) {
            if (typeof e.getModifierState === 'function') {
                capsHint.classList.toggle('hidden', !e.getModifierState('CapsLock'));
            }
        }
        passwordInput.addEventListener('keydown', checkCaps);
        passwordInput.addEventListener('keyup', checkCaps);
        passwordInput.addEventListener('blur', () => capsHint.classList.add('hidden'));

        // Tombol berubah jadi loading saat form dikirim
        form.addEventListener('submit', function() {
            if (!this.checkValidity()) return;

            loginButton.disabled = true;
            loginLabel.textContent = 'Memproses...';
            loginButton.insertAdjacentHTML('afterbegin', '<span class="spinner" aria-hidden="true"></span>');
        });
    </script>

</body>

</html>
