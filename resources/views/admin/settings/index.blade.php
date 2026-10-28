@extends('layout.template')

@section('content')
    <?php
    $storeStatus = $settings['store_status'] ?? 'open';
    $isClosed = $storeStatus === 'closed';
    ?>

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
            animation-delay: calc(var(--i, 0) * 90ms);
        }

        /* ===== Kartu seksi ===== */
        .bn-card {
            transition: box-shadow .3s, border-color .3s, transform .3s cubic-bezier(.22, 1, .36, 1);
        }

        .bn-card:hover {
            box-shadow: 0 18px 36px -22px rgba(117, 74, 48, .45);
            border-color: #ecd8c8;
        }

        .bn-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #fdf0e3;
            border: 1px solid #f0d9c2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: transform .4s cubic-bezier(.34, 1.56, .64, 1);
        }

        .bn-card:hover .bn-card-icon {
            transform: scale(1.12) rotate(-8deg);
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

        /* ===== Pilihan status kedai (radio kartu) ===== */
        .bn-choice {
            display: block;
            cursor: pointer;
        }

        .bn-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .bn-choice-body {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            transition: border-color .25s, background-color .25s, transform .25s cubic-bezier(.34, 1.56, .64, 1), box-shadow .25s;
        }

        .bn-choice:hover .bn-choice-body {
            transform: translateY(-2px);
        }

        .bn-choice input:focus-visible+.bn-choice-body {
            box-shadow: 0 0 0 4px rgba(117, 74, 48, .2);
        }

        .bn-choice-dot {
            width: 12px;
            height: 12px;
            border-radius: 9999px;
            flex: none;
            background: #d1d5db;
            transition: background-color .25s, transform .3s;
        }

        .bn-open input:checked+.bn-choice-body {
            border-color: #16a34a;
            background: #f0fdf4;
            box-shadow: 0 10px 22px -14px rgba(22, 163, 74, .7);
        }

        .bn-open input:checked+.bn-choice-body .bn-choice-dot {
            background: #16a34a;
            animation: bn-pulse-g 1.8s infinite;
        }

        .bn-closed input:checked+.bn-choice-body {
            border-color: #e11d48;
            background: #fff1f2;
            box-shadow: 0 10px 22px -14px rgba(225, 29, 72, .7);
        }

        .bn-closed input:checked+.bn-choice-body .bn-choice-dot {
            background: #e11d48;
            animation: bn-pulse-r 1.8s infinite;
        }

        @keyframes bn-pulse-g {
            0% {
                box-shadow: 0 0 0 0 rgba(22, 163, 74, .55);
            }

            70% {
                box-shadow: 0 0 0 9px rgba(22, 163, 74, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(22, 163, 74, 0);
            }
        }

        @keyframes bn-pulse-r {
            0% {
                box-shadow: 0 0 0 0 rgba(225, 29, 72, .55);
            }

            70% {
                box-shadow: 0 0 0 9px rgba(225, 29, 72, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(225, 29, 72, 0);
            }
        }

        /* ===== Kartu metode pembayaran (checkbox) ===== */
        .bn-pay {
            display: block;
            cursor: pointer;
        }

        .bn-pay input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .bn-pay-body {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            transition: border-color .25s, background-color .25s, transform .25s cubic-bezier(.34, 1.56, .64, 1), box-shadow .25s;
        }

        .bn-pay:hover .bn-pay-body {
            transform: translateY(-2px);
            border-color: #c9a98f;
        }

        .bn-pay input:focus-visible+.bn-pay-body {
            box-shadow: 0 0 0 4px rgba(117, 74, 48, .2);
        }

        .bn-pay-check {
            width: 22px;
            height: 22px;
            border-radius: 8px;
            flex: none;
            border: 2px solid #d1d5db;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color .25s, border-color .25s;
        }

        .bn-pay-check svg {
            width: 14px;
            height: 14px;
            stroke: #fff;
            transform: scale(0) rotate(-45deg);
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1);
        }

        .bn-pay input:checked+.bn-pay-body {
            border-color: #754a30;
            background: #fbf6f2;
            box-shadow: 0 10px 22px -14px rgba(117, 74, 48, .7);
        }

        .bn-pay input:checked+.bn-pay-body .bn-pay-check {
            background: #754a30;
            border-color: #754a30;
        }

        .bn-pay input:checked+.bn-pay-body .bn-pay-check svg {
            transform: scale(1) rotate(0);
        }

        .bn-pay-emoji {
            font-size: 20px;
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1);
        }

        .bn-pay:hover .bn-pay-emoji {
            transform: scale(1.2) rotate(-8deg);
        }

        /* ===== Lencana status di header ===== */
        .bn-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            transition: background-color .3s, color .3s;
        }

        .bn-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
        }

        .bn-live.is-open {
            background: #dcfce7;
            color: #15803d;
        }

        .bn-live.is-open .bn-live-dot {
            background: #16a34a;
            animation: bn-pulse-g 1.8s infinite;
        }

        .bn-live.is-closed {
            background: #ffe4e6;
            color: #be123c;
        }

        .bn-live.is-closed .bn-live-dot {
            background: #e11d48;
            animation: bn-pulse-r 1.8s infinite;
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

        /* ===== Ikon mata & kekuatan password ===== */
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

        .bn-meter-bar {
            transition: width .4s cubic-bezier(.22, 1, .36, 1), background-color .3s;
        }

        /* ===== Alert ===== */
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

        /* ===== Bar simpan melayang ===== */
        .bn-dock {
            position: fixed;
            left: 50%;
            bottom: 20px;
            z-index: 40;
            width: calc(100% - 32px);
            max-width: 640px;
            transform: translate(-50%, 140%);
            opacity: 0;
            pointer-events: none;
            transition: transform .5s cubic-bezier(.34, 1.56, .64, 1), opacity .3s;
        }

        .bn-dock.is-on {
            transform: translate(-50%, 0);
            opacity: 1;
            pointer-events: auto;
        }

        @media (prefers-reduced-motion: reduce) {

            .bn-in,
            .bn-alert {
                animation: none;
                opacity: 1;
            }

            .bn-card,
            .bn-card-icon,
            .bn-field,
            .bn-choice-body,
            .bn-pay-body,
            .bn-pay-check svg,
            .bn-pay-emoji,
            .bn-btn,
            .bn-eye,
            .bn-meter-bar,
            .bn-dock,
            .bn-live {
                transition: none;
            }

            .bn-open input:checked+.bn-choice-body .bn-choice-dot,
            .bn-closed input:checked+.bn-choice-body .bn-choice-dot,
            .bn-live.is-open .bn-live-dot,
            .bn-live.is-closed .bn-live-dot,
            .bn-eye.is-flip svg {
                animation: none;
            }
        }
    </style>

    <div class="w-full p-4 md:p-6 lg:p-8 pb-28">

        <!-- Header Page -->
        <div class="bn-in mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3" style="--i:0">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Pengaturan Sistem &amp; Kedai</h1>
                <p class="text-sm text-gray-500">Konfigurasi operasional kedai, informasi kontak, pembayaran, dan sistem
                    BrewNest.</p>
            </div>
            <span id="live-badge" class="bn-live self-start sm:self-auto {{ $isClosed ? 'is-closed' : 'is-open' }}">
                <span class="bn-live-dot"></span>
                <span id="live-text">{{ $isClosed ? 'Kedai sedang tutup' : 'Kedai sedang buka' }}</span>
            </span>
        </div>

        <!-- Alert Notifikasi -->
        @if (session('success'))
            <div
                class="bn-alert js-alert mb-6 p-4 text-sm text-green-700 bg-green-100 rounded-2xl border border-green-200 flex items-start justify-between gap-3">
                <span class="flex items-center gap-2"><span>✅</span> {{ session('success') }}</span>
                <button type="button" class="js-alert-close text-green-700/70 hover:text-green-900 text-lg leading-none"
                    aria-label="Tutup">&times;</button>
            </div>
        @endif

        <form id="settings-form" action="{{ route('admin.settings.update') }}" method="post" class="w-full">
            @csrf

            <div class="space-y-6 w-full">

                <!-- 1. Operasional Kedai (Status Buka/Tutup & Pajak) -->
                <div class="bn-in bn-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6" style="--i:1">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                        <div class="bn-card-icon">⚙️</div>
                        <div>
                            <h2 class="text-base font-bold text-gray-800">Status Operasional &amp; Pesanan</h2>
                            <p class="text-xs text-gray-500">Atur ketersediaan penerimaan pesanan online.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-1">
                            <span class="block text-sm font-semibold text-gray-700 mb-2">Status Kedai (Online Order)</span>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="bn-choice bn-open relative">
                                    <input type="radio" name="store_status" value="open"
                                        {{ !$isClosed ? 'checked' : '' }}>
                                    <span class="bn-choice-body">
                                        <span class="bn-choice-dot"></span>
                                        <span>
                                            <span class="block text-sm font-semibold text-gray-800">Buka</span>
                                            <span class="block text-xs text-gray-500">Menerima pesanan</span>
                                        </span>
                                    </span>
                                </label>
                                <label class="bn-choice bn-closed relative">
                                    <input type="radio" name="store_status" value="closed"
                                        {{ $isClosed ? 'checked' : '' }}>
                                    <span class="bn-choice-body">
                                        <span class="bn-choice-dot"></span>
                                        <span>
                                            <span class="block text-sm font-semibold text-gray-800">Tutup</span>
                                            <span class="block text-xs text-gray-500">Toko libur / pemesanan dibatasi</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 content-start">
                            <div>
                                <label for="opening_hours" class="block text-sm font-semibold text-gray-700 mb-2">Jam
                                    Operasional</label>
                                <input type="text" id="opening_hours" name="opening_hours"
                                    value="{{ $settings['opening_hours'] ?? '08:00 - 22:00 WIB' }}"
                                    class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                            </div>

                            <div>
                                <label for="tax_percentage" class="block text-sm font-semibold text-gray-700 mb-2">Pajak /
                                    PB1 (%)</label>
                                <div class="relative">
                                    <input type="number" id="tax_percentage" name="tax_percentage"
                                        value="{{ $settings['tax_percentage'] ?? '10' }}" min="0" max="100"
                                        step="any"
                                        class="bn-field w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 font-medium">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Informasi Kedai & Kontak -->
                <div class="bn-in bn-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6" style="--i:2">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                        <div class="bn-card-icon">☕</div>
                        <div>
                            <h2 class="text-base font-bold text-gray-800">Profil &amp; Kontak Kedai</h2>
                            <p class="text-xs text-gray-500">Informasi ini akan ditampilkan di header/footer website
                                pembeli.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
                        <div>
                            <label for="store_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama
                                Kedai</label>
                            <input type="text" id="store_name" name="store_name"
                                value="{{ $settings['store_name'] ?? 'BrewNest Coffee' }}" required
                                class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        </div>

                        <div>
                            <label for="store_tagline" class="block text-sm font-semibold text-gray-700 mb-2">Slogan /
                                Tagline</label>
                            <input type="text" id="store_tagline" name="store_tagline"
                                value="{{ $settings['store_tagline'] ?? 'Hangatnya Kopi, Nyamannya Rumah' }}"
                                class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        </div>

                        <div>
                            <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-2">Nomor WhatsApp
                                / Order Hotline</label>
                            <input type="text" id="phone_number" name="phone_number"
                                value="{{ $settings['phone_number'] ?? '+62 812-3456-7890' }}"
                                class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-2">Email
                                Kontak</label>
                            <input type="email" id="contact_email" name="contact_email"
                                value="{{ $settings['contact_email'] ?? 'hello@brewnest.com' }}"
                                class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="store_address" class="block text-sm font-semibold text-gray-700 mb-2">Alamat
                            Kedai</label>
                        <textarea id="store_address" name="store_address" rows="3"
                            class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none resize-y">{{ $settings['store_address'] ?? 'Jl. Kampus Unilam No. 12, Serang, Banten' }}</textarea>
                    </div>
                </div>

                <!-- 3 & 4: Pembayaran + Akun (berdampingan di layar lebar) -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 w-full">

                    <!-- 3. Metode Pembayaran -->
                    <div class="bn-in bn-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6"
                        style="--i:3">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                            <div class="bn-card-icon">💳</div>
                            <div>
                                <h2 class="text-base font-bold text-gray-800">Metode Pembayaran Ditampilkan</h2>
                                <p class="text-xs text-gray-500">Pilih opsi pembayaran yang diizinkan untuk pelanggan saat
                                    checkout.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-1 gap-4">
                            <label class="bn-pay relative">
                                <input type="hidden" name="pay_qris" value="0">
                                <input type="checkbox" name="pay_qris" value="1"
                                    {{ (string) old('pay_qris', $settings['pay_qris'] ?? '0') === '1' ? 'checked' : '' }}>
                                <span class="bn-pay-body">
                                    <span class="bn-pay-check"><svg fill="none" viewBox="0 0 24 24"
                                            stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg></span>
                                    <span class="bn-pay-emoji">📱</span>
                                    <span class="text-sm font-medium text-gray-700">QRIS / E-Wallet</span>
                                </span>
                            </label>

                            <label class="bn-pay relative">
                                <input type="hidden" name="pay_cash" value="0">
                                <input type="checkbox" name="pay_cash" value="1"
                                    {{ (string) old('pay_cash', $settings['pay_cash'] ?? '0') === '1' ? 'checked' : '' }}>
                                <span class="bn-pay-body">
                                    <span class="bn-pay-check"><svg fill="none" viewBox="0 0 24 24"
                                            stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg></span>
                                    <span class="bn-pay-emoji">💵</span>
                                    <span class="text-sm font-medium text-gray-700">Bayar di Kasir (Cash)</span>
                                </span>
                            </label>

                            <label class="bn-pay relative">
                                <input type="hidden" name="pay_transfer" value="0">
                                <input type="checkbox" name="pay_transfer" value="1"
                                    {{ (string) old('pay_transfer', $settings['pay_transfer'] ?? '0') === '1' ? 'checked' : '' }}>
                                <span class="bn-pay-body">
                                    <span class="bn-pay-check"><svg fill="none" viewBox="0 0 24 24"
                                            stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg></span>
                                    <span class="bn-pay-emoji">🏦</span>
                                    <span class="text-sm font-medium text-gray-700">Transfer Bank</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- 4. Keamanan Akun Admin -->
                    <div class="bn-in bn-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6"
                        style="--i:4">
                        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                            <div class="bn-card-icon">🔒</div>
                            <div>
                                <h2 class="text-base font-bold text-gray-800">Akun Administrator</h2>
                                <p class="text-xs text-gray-500">Pengaturan akses login akun kamu saat ini.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="username"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                                    <input type="text" id="username" name="username"
                                        value="{{ $user['username'] ?? 'admin' }}" required
                                        class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email
                                        Admin</label>
                                    <input type="email" id="email" name="email"
                                        value="{{ $user['email'] ?? 'admin@caffe.com' }}" required
                                        class="bn-field w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label for="passwordInput" class="block text-sm font-semibold text-gray-700 mb-2">Ganti
                                    Password <span class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <input type="password" id="passwordInput" name="password" placeholder="••••••••"
                                        autocomplete="new-password"
                                        class="bn-field w-full px-4 py-2.5 pr-12 border border-gray-200 rounded-xl focus:outline-none">
                                    <button type="button" id="togglePassword" aria-label="Tampilkan password"
                                        class="bn-eye absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-[#754a30] focus:outline-none">
                                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                                <div id="pwMeter" class="mt-2 h-5 opacity-0 transition-opacity duration-300">
                                    <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                        <div id="pwBar" class="bn-meter-bar h-full w-0 rounded-full bg-rose-500">
                                        </div>
                                    </div>
                                    <p id="pwLabel" class="mt-1 text-xs text-gray-500"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tombol Simpan -->
                <div class="bn-in flex justify-end gap-3 pt-2" style="--i:5">
                    <button type="submit" id="submit-btn"
                        class="bn-btn px-8 py-3 bg-[#754a30] text-white text-sm font-bold rounded-xl hover:bg-[#21140e] shadow-md inline-flex items-center justify-center gap-2">
                        <span id="submit-spinner" class="bn-spinner hidden"></span>
                        <span id="submit-label">💾 Simpan Status Kedai</span>
                    </button>
                </div>

            </div>
        </form>

    </div>

    <!-- Bar simpan melayang: muncul saat ada perubahan -->
    <div id="save-dock" class="bn-dock" aria-live="polite">
        <div class="flex items-center justify-between gap-3 bg-gray-900 text-white rounded-2xl shadow-2xl px-4 py-3">
            <p class="text-sm"><span class="mr-1">✏️</span> Ada perubahan yang belum disimpan</p>
            <div class="flex items-center gap-2">
                <button type="button" id="dock-reset"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white/10 hover:bg-white/20 transition">Batalkan</button>
                <button type="button" id="dock-save"
                    class="px-4 py-1.5 text-xs font-bold rounded-lg bg-[#c98a5b] text-gray-900 hover:bg-[#e0a373] transition">Simpan</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('settings-form');

            // ===== Alert: tutup manual + otomatis hilang =====
            function closeAlert(el) {
                el.classList.add('is-closing');
                setTimeout(() => el.remove(), 450);
            }
            document.querySelectorAll('.js-alert').forEach(el => {
                el.querySelector('.js-alert-close').addEventListener('click', () => closeAlert(el));
                setTimeout(() => closeAlert(el), 6000);
            });

            // ===== Lencana status kedai di header =====
            const badge = document.getElementById('live-badge');
            const badgeText = document.getElementById('live-text');
            document.querySelectorAll('input[name="store_status"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    const closed = radio.value === 'closed' && radio.checked;
                    badge.classList.toggle('is-closed', closed);
                    badge.classList.toggle('is-open', !closed);
                    badgeText.textContent = closed ? 'Kedai sedang tutup' : 'Kedai sedang buka';
                });
            });

            // ===== Toggle password =====
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');

            const ICON_HIDDEN =
                `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>`;
            const ICON_SHOWN =
                `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;

            togglePassword.addEventListener('click', () => {
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
            passwordInput.addEventListener('input', () => {
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

            // ===== Deteksi perubahan (dirty state) =====
            const dock = document.getElementById('save-dock');

            function snapshot() {
                return JSON.stringify(Array.from(new FormData(form).entries()));
            }
            const initial = snapshot();
            let submitting = false;

            function checkDirty() {
                dock.classList.toggle('is-on', snapshot() !== initial);
            }
            form.addEventListener('input', checkDirty);
            form.addEventListener('change', checkDirty);

            document.getElementById('dock-reset').addEventListener('click', () => {
                form.reset();
                const checked = form.querySelector('input[name="store_status"]:checked');
                if (checked) checked.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                checkDirty();
            });

            document.getElementById('dock-save').addEventListener('click', () => {
                form.requestSubmit();
            });

            form.addEventListener('submit', () => {
                if (!form.checkValidity()) return;
                submitting = true;
                dock.classList.remove('is-on');
                const btn = document.getElementById('submit-btn');
                btn.classList.add('is-loading');
                document.getElementById('submit-spinner').classList.remove('hidden');
                document.getElementById('submit-label').textContent = 'Menyimpan...';
            });
        })();
    </script>
@endsection
