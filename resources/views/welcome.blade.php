@php
    // Admin yang sedang login (untuk link footer)
    $isAdmin = auth()->check() && in_array(auth()->user()->role ?? '', ['admin', 'staff'], true);
    $isCafeOpen = strtolower(trim((string) ($storeStatus ?? 'open'))) !== 'closed';

    // ===== Pengaturan kedai dari Admin > Settings (tabel settings) =====
    $settings = collect($settings ?? [])->all();
    // Pakai nilai setting kalau terisi, kalau kosong pakai nilai default
    $s = fn($key, $default = '') => filled($settings[$key] ?? null) ? $settings[$key] : $default;

    $storeName = $s('store_name', 'BrewNest Coffee');
    $storeTagline = $s('store_tagline', 'Kopi enak, suasana hangat, kebersamaan yang lebih baik.');
    $openHours = $s('opening_hours', '07.00 - 22.00');
    $storePhone = $s('phone_number', '(123) 456-7890');
    $phoneHref = preg_replace('/[^\d+]/', '', (string) $storePhone); // untuk link tel:
    $storeEmail = $s('contact_email', 'hello@brewnest.com');
    $storeAddress = $s('store_address', '123 Coffee Street, Brooklyn, NY 11201');
    $taxPercent = (float) $s('tax_percentage', 11);
    $taxLabel = rtrim(rtrim(number_format($taxPercent, 2, '.', ''), '0'), '.');

    // URL peta (otomatis ikut alamat dari Admin > Settings)
    $mapQuery = urlencode((string) $storeAddress);
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . $mapQuery . '&hl=id&z=16&output=embed';
    $mapOpenUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapQuery;

    // Mendukung array maupun Eloquent model / stdClass
    $toArr = fn($row) => is_array($row) ? $row : (method_exists($row, 'toArray') ? $row->toArray() : (array) $row);

    $menus = collect($menus ?? [])
        ->map($toArr)
        ->values()
        ->all();
    $categories = collect($categories ?? [])
        ->map($toArr)
        ->values()
        ->all();
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $storeName . ' | Temukan Kopi Favoritmu' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('kopi.jpeg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,500;1,600&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coffee: '#1d110b',
                        brown: '#754a30',
                        caramel: '#b9854f',
                        tan: '#d9a46a',
                        paper: '#fbf8f3',
                        cream: '#f6f0e7',
                        sand: '#ece2d4'
                    },
                    fontFamily: {
                        display: ['"Playfair Display"', 'Georgia', 'serif']
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
        }

        /* Cegah geser samping & kilatan saat tap di HP */
        html,
        body {
            overflow-x: clip;
        }

        body {
            -webkit-tap-highlight-color: transparent;
        }

        /* Ikon (sprite SVG) */
        .ic {
            width: 1.25rem;
            height: 1.25rem;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.6;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        /* Gradasi krem di sisi kiri foto hero (desktop) */
        .hero-fade {
            background: linear-gradient(90deg, #fbf8f3 0%, rgba(251, 248, 243, .85) 18%, rgba(251, 248, 243, 0) 48%);
        }

        .no-scrollbar {
            scrollbar-width: none;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .menu-card {
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 22px rgba(29, 17, 11, .1);
        }

        /* #reservasi (div) dan #kontak (footer) ikut diberi jarak
           supaya tidak tertutup navbar sticky saat diklik */
        section[id],
        #reservasi,
        #kontak {
            scroll-margin-top: 80px;
        }
    </style>

    <!-- ===================== ANIMASI (CSS) ===================== -->
    <style>
        @keyframes revealUp {
            from {
                opacity: 0;
                transform: translateY(32px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes revealLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes revealRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes revealZoom {
            from {
                opacity: 0;
                transform: scale(.85);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes kenburns {
            from {
                transform: scale(1.15);
            }

            to {
                transform: scale(1);
            }
        }

        @keyframes floatY {

            0%,
            100% {
                transform: translateY(0) rotate(0);
            }

            50% {
                transform: translateY(-5px) rotate(12deg);
            }
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 200% 50%;
            }
        }

        @keyframes bump {
            0% {
                transform: scale(1);
            }

            40% {
                transform: scale(1.7);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes popIn {
            from {
                opacity: 0;
                transform: translateY(24px) scale(.94);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes slideRow {
            from {
                opacity: 0;
                transform: translateX(24px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes steam {
            0% {
                opacity: 0;
                transform: translateY(0) scaleX(1);
            }

            30% {
                opacity: .55;
            }

            100% {
                opacity: 0;
                transform: translateY(-34px) scaleX(1.6);
            }
        }

        @keyframes pulseRing {
            0% {
                box-shadow: 0 0 0 0 rgba(185, 133, 79, .55);
            }

            100% {
                box-shadow: 0 0 0 14px rgba(185, 133, 79, 0);
            }
        }

        /* ---------- HERO (otomatis saat halaman dibuka) ---------- */
        #home .max-w-xl>* {
            animation: revealUp .9s cubic-bezier(.2, .7, .2, 1) backwards;
        }

        #home .max-w-xl> :nth-child(1) {
            animation-delay: .1s;
        }

        #home .max-w-xl> :nth-child(2) {
            animation-delay: .25s;
        }

        #home .max-w-xl> :nth-child(3) {
            animation-delay: .4s;
        }

        #home .max-w-xl> :nth-child(4) {
            animation-delay: .55s;
        }

        #home img.object-cover {
            animation: kenburns 2.4s cubic-bezier(.2, .7, .2, 1) both;
        }

        #home .max-w-xl>p:first-child svg {
            animation: floatY 3.2s ease-in-out infinite;
        }

        /* kata "Setiap" jadi gradasi emas yang bergerak */
        #home h1 span {
            background: linear-gradient(90deg, #b9854f, #e8c089, #754a30, #b9854f);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradientShift 4s linear infinite;
        }

        /* fitur di bawah hero */
        #home .grid>div {
            animation: revealUp .8s cubic-bezier(.2, .7, .2, 1) backwards;
        }

        #home .grid>div:nth-child(1) {
            animation-delay: .7s;
        }

        #home .grid>div:nth-child(2) {
            animation-delay: .8s;
        }

        #home .grid>div:nth-child(3) {
            animation-delay: .9s;
        }

        #home .grid>div:nth-child(4) {
            animation-delay: 1s;
        }

        #home .grid>div svg {
            transition: transform .4s ease;
        }

        #home .grid>div:hover svg {
            transform: translateY(-4px) rotate(-10deg) scale(1.15);
        }

        /* uap kopi di atas tombol */
        .steam-wrap {
            position: relative;
            display: inline-flex;
        }

        .steam-wrap i {
            position: absolute;
            bottom: 100%;
            width: 3px;
            height: 14px;
            border-radius: 9px;
            background: rgba(185, 133, 79, .7);
            filter: blur(2px);
            animation: steam 2.4s ease-in infinite;
            opacity: 0;
        }

        .steam-wrap i:nth-child(1) {
            left: 30%;
            animation-delay: 0s;
        }

        .steam-wrap i:nth-child(2) {
            left: 50%;
            animation-delay: .8s;
        }

        .steam-wrap i:nth-child(3) {
            left: 70%;
            animation-delay: 1.6s;
        }

        /* ---------- Tombol: kilau + denyut ---------- */
        .btn-shine {
            position: relative;
            overflow: hidden;
        }

        .btn-shine::after {
            content: '';
            position: absolute;
            top: 0;
            left: -80%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, .35), transparent);
            transform: skewX(-20deg);
            transition: left .7s ease;
        }

        .btn-shine:hover::after {
            left: 130%;
        }

        #cartBtn {
            animation: pulseRing 2.6s ease-out infinite;
        }

        /* ---------- Navbar & progress bar ---------- */
        header.sticky {
            transition: box-shadow .3s ease, background-color .3s ease;
        }

        header.is-scrolled {
            box-shadow: 0 6px 24px rgba(29, 17, 11, .08);
        }

        #scrollBar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 100%;
            z-index: 80;
            background: linear-gradient(90deg, #754a30, #d9a46a);
            transform: scaleX(0);
            transform-origin: left;
            pointer-events: none;
        }

        /* Link aktif ditentukan scrollspy (kelas .is-active) */
        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 100%;
            background: #b9854f;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .3s ease;
        }

        .nav-link:hover::after {
            transform: scaleX(1);
        }

        .nav-link.is-active {
            color: #1d110b;
        }

        .nav-link.is-active::after {
            transform: scaleX(1);
            background: #1d110b;
        }

        .nav-link-m.is-active {
            color: #b9854f;
        }

        /* ---------- Scroll reveal ---------- */
        .reveal {
            opacity: 0;
        }

        .reveal.in {
            opacity: 1;
            animation: revealUp .85s cubic-bezier(.2, .7, .2, 1) backwards;
            animation-delay: var(--d, 0s);
        }

        .reveal.from-left.in {
            animation-name: revealLeft;
        }

        .reveal.from-right.in {
            animation-name: revealRight;
        }

        .reveal.zoom.in {
            animation-name: revealZoom;
        }

        /* ---------- Kartu menu & gambar ---------- */
        .menu-card img {
            transition: transform .6s cubic-bezier(.2, .7, .2, 1);
        }

        .menu-card:hover img {
            transform: scale(1.08);
        }

        .menu-card>div:first-child {
            overflow: hidden;
            border-radius: .6rem;
        }

        .order-btn {
            transition: transform .3s ease, background-color .3s ease;
        }

        .menu-card:hover .order-btn {
            transform: rotate(90deg);
        }

        .order-btn:active {
            transform: scale(.85) rotate(90deg);
        }

        .menu-card.pop {
            animation: revealZoom .5s cubic-bezier(.2, .7, .2, 1) backwards;
            animation-delay: var(--d, 0s);
        }

        /* Instagram */
        .ig-img {
            transition: transform .5s ease, filter .5s ease;
        }

        .ig-img:hover {
            transform: scale(1.06) rotate(-1.5deg);
            filter: saturate(1.2);
        }

        /* Kartu "Tentang" */
        #tentang .grid>div {
            transition: transform .4s ease, box-shadow .4s ease;
        }

        #tentang .grid>div:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 36px rgba(29, 17, 11, .14);
        }

        #tentang .grid>div>img {
            transition: transform 1.2s ease;
        }

        #tentang .grid>div:hover>img {
            transform: scale(1.06);
        }

        /* Footer link geser (hanya daftar tautan, tidak ikut tombol peta) */
        footer ul a {
            display: inline-block;
            transition: transform .25s ease, color .25s ease;
        }

        footer ul a:hover {
            transform: translateX(5px);
        }

        /* ---------- Badge keranjang ---------- */
        .cart-count.bump {
            animation: bump .45s ease;
        }

        /* ---------- Modal keranjang ---------- */
        #cartModal.flex {
            animation: fadeIn .25s ease;
        }

        #cartModal.flex>div {
            animation: popIn .4s cubic-bezier(.2, .8, .2, 1);
        }

        #cartItemsList>div {
            animation: slideRow .45s cubic-bezier(.2, .8, .2, 1) backwards;
        }

        #cartItemsList>div:nth-child(1) {
            animation-delay: .05s;
        }

        #cartItemsList>div:nth-child(2) {
            animation-delay: .1s;
        }

        #cartItemsList>div:nth-child(3) {
            animation-delay: .15s;
        }

        #cartItemsList>div:nth-child(4) {
            animation-delay: .2s;
        }

        #cartItemsList>div:nth-child(5) {
            animation-delay: .25s;
        }

        /* ---------- Toast meluncur dari bawah ---------- */
        #toast {
            transform: translate(-50%, 20px);
            transition: opacity .3s ease, transform .3s ease;
        }

        #toast:not(.opacity-0) {
            transform: translate(-50%, 0);
        }

        /* ---------- Gambar terbang ke keranjang ---------- */
        .fly-img {
            position: fixed;
            z-index: 90;
            border-radius: 9999px;
            object-fit: cover;
            pointer-events: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .3);
        }

        /* ---------- Hormati pengguna yang mematikan animasi ---------- */
        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }

            .reveal {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-paper text-coffee antialiased">

    <!-- Sprite ikon -->
    <svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0" aria-hidden="true">
        <symbol id="i-coffee" viewBox="0 0 24 24">
            <path d="M10 2v2M14 2v2M6 2v2" />
            <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1" />
        </symbol>
        <symbol id="i-utensils" viewBox="0 0 24 24">
            <path d="M3 2v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V2M7 2v20" />
            <path d="M21 15V2a5 5 0 0 0-5 5v6a2 2 0 0 0 2 2h3Zm0 0v7" />
        </symbol>
        <symbol id="i-sofa" viewBox="0 0 24 24">
            <path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3" />
            <path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z" />
            <path d="M4 18v2M20 18v2" />
        </symbol>
        <symbol id="i-wifi" viewBox="0 0 24 24">
            <path d="M5 12.55a11 11 0 0 1 14.08 0M1.42 9a16 16 0 0 1 21.16 0M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01" />
        </symbol>
        <symbol id="i-leaf" viewBox="0 0 24 24">
            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" />
        </symbol>
        <symbol id="i-beans" viewBox="0 0 24 24">
            <circle cx="8" cy="8" r="5" />
            <path d="M5.5 10.5c1.5-.5 3-2 3-3.5" />
            <circle cx="16" cy="16" r="5" />
            <path d="M13.5 18.5c1.5-.5 3-2 3-3.5" />
        </symbol>
        <symbol id="i-star" viewBox="0 0 24 24">
            <polygon
                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </symbol>
        <symbol id="i-heart" viewBox="0 0 24 24">
            <path
                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
        </symbol>
        <symbol id="i-cart" viewBox="0 0 24 24">
            <circle cx="8" cy="21" r="1" />
            <circle cx="19" cy="21" r="1" />
            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
        </symbol>
        <symbol id="i-calendar" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <path d="M16 2v4M8 2v4M3 10h18" />
        </symbol>
        <symbol id="i-clock" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 6v6l4 2" />
        </symbol>
        <symbol id="i-pin" viewBox="0 0 24 24">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
            <circle cx="12" cy="10" r="3" />
        </symbol>
        <symbol id="i-phone" viewBox="0 0 24 24">
            <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" />
        </symbol>
        <symbol id="i-mail" viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="16" rx="2" />
            <path d="m22 7-10 5L2 7" />
        </symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7" />
        </symbol>
        <symbol id="i-left" viewBox="0 0 24 24">
            <path d="m15 18-6-6 6-6" />
        </symbol>
        <symbol id="i-right" viewBox="0 0 24 24">
            <path d="m9 18 6-6-6-6" />
        </symbol>
        <symbol id="i-plus" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5v14" />
        </symbol>
        <symbol id="i-facebook" viewBox="0 0 24 24">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
        </symbol>
        <symbol id="i-instagram" viewBox="0 0 24 24">
            <rect x="2" y="2" width="20" height="20" rx="5" />
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37zM17.5 6.5h.01" />
        </symbol>
        <symbol id="i-twitter" viewBox="0 0 24 24">
            <path
                d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
        </symbol>
        <symbol id="i-menu" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </symbol>
        <symbol id="i-close" viewBox="0 0 24 24">
            <path d="M18 6 6 18M6 6l12 12" />
        </symbol>
        <symbol id="i-user" viewBox="0 0 24 24">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </symbol>
    </svg>

    <!-- TOP BAR -->
    <div class="hidden lg:block bg-coffee text-stone-300 text-[11px]">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-10 py-2.5">
            <span class="flex items-center gap-2"><svg class="ic h-3.5 w-3.5">
                    <use href="#i-pin" />
                </svg> {{ $storeAddress }}</span>
            <span class="flex items-center gap-2"><svg class="ic h-3.5 w-3.5">
                    <use href="#i-clock" />
                </svg> Setiap hari: {{ $openHours }}</span>
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-2"><svg class="ic h-3.5 w-3.5">
                        <use href="#i-phone" />
                    </svg> {{ $storePhone }}</span>
                <span class="flex items-center gap-3">
                    <a href="https://www.facebook.com/brewnestphilly" aria-label="Facebook"
                        class="hover:text-tan"><svg class="ic h-3.5 w-3.5">
                            <use href="#i-facebook" />
                        </svg></a>
                    <a href="https://www.instagram.com/brewnestphilly" aria-label="Instagram"
                        class="hover:text-tan"><svg class="ic h-3.5 w-3.5">
                            <use href="#i-instagram" />
                        </svg></a>
                    <a href="https://x.com/brewnestphilly" aria-label="Twitter" class="hover:text-tan"><svg
                            class="ic h-3.5 w-3.5">
                            <use href="#i-twitter" />
                        </svg></a>
                </span>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <header class="sticky top-0 z-50 border-b border-stone-200/70 bg-paper/95 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-3.5 lg:px-10">

            <a href="#home" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl border border-coffee/70"><svg
                        class="ic h-6 w-6">
                        <use href="#i-coffee" />
                    </svg></span>
                <span>
                    <span class="block font-display text-2xl font-semibold leading-none">BrewNest</span>
                    <span class="mt-1 block text-[10px] font-semibold tracking-[2.5px] text-stone-500">COFFEE &amp;
                        EATERY</span>
                </span>
            </a>

            <!-- Menu desktop: tampil mulai layar >= 1024px -->
            <div class="hidden items-center gap-6 text-[11px] font-semibold uppercase tracking-wider lg:flex xl:gap-9">
                <a href="#home" data-spy="home"
                    class="nav-link is-active pb-1 text-stone-600 transition hover:text-caramel">Beranda</a>
                <a href="#menu" data-spy="menu"
                    class="nav-link pb-1 text-stone-600 transition hover:text-caramel">Menu</a>
                <a href="#tentang" data-spy="tentang"
                    class="nav-link pb-1 text-stone-600 transition hover:text-caramel">Tentang Kami</a>
                <a href="#kontak" data-spy="kontak"
                    class="nav-link pb-1 text-stone-600 transition hover:text-caramel">Kontak</a>
            </div>

            <div class="flex items-center gap-3">
                <!-- Satu tombol Login saja (tanpa Dashboard / Keluar) -->
                <a href="{{ route('login') }}"
                    class="hidden items-center gap-2 rounded-full border border-coffee px-5 py-3 text-[11px] font-bold uppercase tracking-wider transition hover:bg-coffee hover:text-white lg:flex">
                    <svg class="ic h-4 w-4">
                        <use href="#i-user" />
                    </svg> Login
                </a>

                <button id="cartBtn" type="button"
                    class="hidden items-center gap-2.5 rounded-full bg-coffee px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-white transition hover:bg-brown disabled:cursor-not-allowed disabled:opacity-60 lg:flex"
                    @disabled(!$isCafeOpen)
                    aria-label="{{ $isCafeOpen ? 'Pesan online' : 'Maaf, cafe kami sedang tutup' }}">
                    {{ $isCafeOpen ? 'Pesan Online' : 'Cafe Tutup' }}
                    <svg class="ic h-4 w-4">
                        <use href="#i-cart" />
                    </svg>
                    <span
                        class="cart-count rounded-full bg-tan px-1.5 py-0.5 text-[10px] font-black text-coffee">0</span>
                </button>

                <button id="cartBtnTop" type="button"
                    class="relative disabled:cursor-not-allowed disabled:opacity-60 lg:hidden"
                    aria-label="{{ $isCafeOpen ? 'Buka keranjang' : 'Maaf, cafe kami sedang tutup' }}"
                    @disabled(!$isCafeOpen)>
                    <svg class="ic h-6 w-6">
                        <use href="#i-cart" />
                    </svg>
                    <span
                        class="cart-count absolute -right-2 -top-2 rounded-full bg-caramel px-1.5 text-[10px] font-black text-white">0</span>
                </button>

                <button id="menuToggle" type="button" class="lg:hidden" aria-label="Buka menu"
                    aria-expanded="false" aria-controls="mobileMenu">
                    <svg class="ic h-6 w-6">
                        <use href="#i-menu" />
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Menu mobile/tablet: JS menambah/menghapus "hidden" dan "flex" -->
        <div id="mobileMenu"
            class="hidden max-h-[calc(100dvh-4.5rem)] flex-col gap-1 overflow-y-auto border-t border-stone-200 bg-paper px-6 pb-6 pt-4 text-sm font-semibold lg:hidden">
            <a href="#home" data-spy="home" class="nav-link-m py-2">Beranda</a>
            <a href="#menu" data-spy="menu" class="nav-link-m py-2">Menu</a>
            <a href="#tentang" data-spy="tentang" class="nav-link-m py-2">Tentang Kami</a>
            <a href="#kontak" data-spy="kontak" class="nav-link-m py-2">Kontak</a>
            <a href="{{ route('login') }}"
                class="mt-3 rounded-full bg-coffee py-3 text-center text-xs font-bold text-white">Login</a>
        </div>
    </header>

    <!-- HERO -->
    <section id="home" class="relative overflow-hidden bg-paper">
        <div class="relative h-60 w-full sm:h-72 lg:absolute lg:inset-y-0 lg:right-0 lg:h-auto lg:w-[66%]">
            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1600&q=85"
                alt="Secangkir latte art BrewNest" class="h-full w-full object-cover">
            <div class="hero-fade absolute inset-0 hidden lg:block"></div>
            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-paper to-transparent lg:hidden"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-5 lg:px-10">
            <div class="max-w-xl pb-10 pt-4 lg:pb-52 lg:pt-20">
                <p class="mb-5 flex items-center gap-2 font-display text-base italic text-caramel">
                    Good Coffee, Good Vibes <svg class="ic h-5 w-5">
                        <use href="#i-leaf" />
                    </svg>
                </p>
                <h1 class="font-display text-4xl font-semibold leading-[1.15] sm:text-5xl md:text-6xl">
                    Kopi Terbaik untuk <span class="text-caramel">Setiap</span> Momen
                </h1>
                <p class="mt-6 max-w-sm text-sm leading-relaxed text-stone-700">
                    Rasakan kehangatan setiap tegukan. Kopi diseduh segar, makanan lezat, dan suasana nyaman, setiap
                    hari di BrewNest.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#menu"
                        class="flex flex-1 items-center justify-center gap-2 rounded-md bg-coffee px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider text-white transition hover:bg-brown sm:flex-none">
                        Lihat Menu <svg class="ic h-4 w-4">
                            <use href="#i-coffee" />
                        </svg>
                    </a>
                    <a href="#reservasi"
                        class="flex flex-1 items-center justify-center gap-2 rounded-md border border-stone-300 bg-paper/80 px-6 py-3.5 text-[11px] font-bold uppercase tracking-wider transition hover:border-coffee sm:flex-none">
                        Pesan Meja <svg class="ic h-4 w-4">
                            <use href="#i-calendar" />
                        </svg>
                    </a>
                </div>
                @if (!$isCafeOpen || session()->has('error'))
                    <p role="status"
                        class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                        {{ session('error', 'Maaf, cafe kami sedang tutup. Silakan pesan kembali saat cafe sudah buka.') }}
                    </p>
                @endif
            </div>

            <!-- Keunggulan -->
            <div
                class="grid grid-cols-2 gap-5 bg-paper pb-8 lg:absolute lg:bottom-0 lg:left-0 lg:w-[60%] lg:grid-cols-4 lg:gap-0 lg:divide-x lg:divide-stone-200 lg:rounded-tr-3xl lg:px-0 lg:pb-8 lg:pt-8 lg:pr-4 lg:shadow-none">
                <div class="flex items-start gap-3 lg:px-5 lg:first:pl-0">
                    <svg class="ic mt-0.5 h-6 w-6 text-caramel">
                        <use href="#i-coffee" />
                    </svg>
                    <div>
                        <p class="text-[11px] font-bold uppercase leading-tight">Kopi Seduh Segar</p>
                        <p class="mt-1 text-[11px] leading-snug text-stone-500">Dari biji pilihan berkualitas</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 lg:px-5">
                    <svg class="ic mt-0.5 h-6 w-6 text-caramel">
                        <use href="#i-utensils" />
                    </svg>
                    <div>
                        <p class="text-[11px] font-bold uppercase leading-tight">Makanan Lezat</p>
                        <p class="mt-1 text-[11px] leading-snug text-stone-500">Dari sarapan hingga dessert</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 lg:px-5">
                    <svg class="ic mt-0.5 h-6 w-6 text-caramel">
                        <use href="#i-sofa" />
                    </svg>
                    <div>
                        <p class="text-[11px] font-bold uppercase leading-tight">Suasana Nyaman</p>
                        <p class="mt-1 text-[11px] leading-snug text-stone-500">Pas untuk santai, kerja, atau bertemu
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3 lg:px-5">
                    <svg class="ic mt-0.5 h-6 w-6 text-caramel">
                        <use href="#i-wifi" />
                    </svg>
                    <div>
                        <p class="text-[11px] font-bold uppercase leading-tight">WiFi Gratis</p>
                        <p class="mt-1 text-[11px] leading-snug text-stone-500">Tetap terhubung sambil menikmati kopi
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MENU -->
    <section id="menu" class="bg-paper px-5 pb-16 pt-16 md:px-10">
        <div class="mx-auto max-w-7xl">

            <div class="mb-8 text-center">
                <p class="mb-2 font-display text-sm italic text-caramel">— Menu Pilihan —</p>
                <h2 class="font-display text-3xl font-semibold md:text-4xl">
                    Dibuat dengan <span class="italic text-caramel">cinta</span>, khusus untuk kamu.
                </h2>
            </div>

            <!-- Filter kategori (HP: satu baris bisa digeser) -->
            <div
                class="no-scrollbar -mx-5 mb-6 flex items-center gap-2 overflow-x-auto px-5 md:mx-0 md:mb-8 md:flex-wrap md:justify-center md:overflow-visible md:px-0">
                <button type="button"
                    class="category-btn shrink-0 whitespace-nowrap rounded-full border border-coffee bg-coffee px-5 py-2 text-xs font-bold text-white transition"
                    data-category="all">Semua</button>
                @foreach ($categories as $cat)
                    @php
                        $catName = $cat['name'] ?? ($cat['nama_kategori'] ?? '');
                        $catId = $cat['id'] ?? $catName;
                    @endphp
                    <button type="button"
                        class="category-btn shrink-0 whitespace-nowrap rounded-full border border-stone-200 bg-white px-5 py-2 text-xs font-bold text-stone-600 transition hover:border-coffee"
                        data-category="{{ $catId }}" data-category-name="{{ $catName }}">
                        {{ $catName }}
                    </button>
                @endforeach
            </div>

            @if (!empty($menus))
                @php
                    $fallbackImages = [
                        'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=600&q=80',
                        'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80',
                    ];
                @endphp
                <div class="relative md:px-12">
                    <button type="button" id="menuPrev" aria-label="Geser ke kiri"
                        class="absolute left-0 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 place-items-center rounded-full border border-stone-300 bg-white shadow-sm transition hover:bg-coffee hover:text-white md:grid"><svg
                            class="ic h-4 w-4">
                            <use href="#i-left" />
                        </svg></button>
                    <button type="button" id="menuNext" aria-label="Geser ke kanan"
                        class="absolute right-0 top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 place-items-center rounded-full border border-stone-300 bg-white shadow-sm transition hover:bg-coffee hover:text-white md:grid"><svg
                            class="ic h-4 w-4">
                            <use href="#i-right" />
                        </svg></button>

                    <div id="menuTrack"
                        class="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-4 pt-1">
                        @foreach ($menus as $index => $item)
                            @php
                                // Kategori bisa berupa relasi (array berisi "name") atau teks biasa
                                $catRel = $item['category'] ?? null;
                                $catRelName = is_array($catRel) ? $catRel['name'] ?? '' : (string) $catRel;

                                $menuName = $item['name'] ?? ($item['title'] ?? ($item['nama_menu'] ?? 'Menu Coffee'));
                                $menuDesc =
                                    $item['description'] ??
                                    ($item['deskripsi'] ?? 'Racikan pilihan lezat dari BrewNest Coffee.');
                                $menuPrice = (float) ($item['price'] ?? ($item['harga'] ?? 0));

                                // Kumpulkan nilai kategori yang mungkin (ID maupun nama),
                                // supaya filter tetap cocok apa pun isi kolom di database.
                                $catTokens = [];
                                foreach (
                                    [
                                        $item['category_id'] ?? null,
                                        $item['kategori_id'] ?? null,
                                        $catRelName,
                                        $item['category_name'] ?? null,
                                        $item['nama_kategori'] ?? null,
                                        $item['kategori'] ?? null,
                                    ]
                                    as $v
                                ) {
                                    if ($v !== null && !is_array($v) && trim((string) $v) !== '') {
                                        $catTokens[] = mb_strtolower(trim((string) $v));
                                    }
                                }
                                $catTokens = implode('|', array_unique($catTokens));

                                $defaultImage = $fallbackImages[$index % count($fallbackImages)];
                                $imgField = (string) ($item['image'] ?? ($item['foto'] ?? ''));

                                // Foto disimpan sebagai path relatif, mis. "uploads/menus/123_foto.jpg"
                                if (
                                    $imgField !== '' &&
                                    str_starts_with($imgField, 'uploads/') &&
                                    !str_contains($imgField, '..') &&
                                    file_exists(public_path($imgField))
                                ) {
                                    $imgSrc = asset($imgField);
                                } elseif (
                                    $imgField !== '' &&
                                    file_exists(public_path('uploads/menus/' . basename($imgField)))
                                ) {
                                    $imgSrc = asset('uploads/menus/' . basename($imgField));
                                } else {
                                    $imgSrc = $defaultImage;
                                }
                            @endphp
                            <article
                                class="menu-card menu-item flex w-40 shrink-0 snap-start flex-col justify-between rounded-xl border border-stone-200/80 bg-white p-2 sm:w-48"
                                data-cats="{{ $catTokens }}">
                                <div>
                                    <img src="{{ $imgSrc }}" alt="{{ $menuName }}" loading="lazy"
                                        class="h-32 w-full rounded-lg object-cover">
                                    <div class="px-1.5 pt-3">
                                        <h3 class="text-[13px] font-bold">{{ $menuName }}</h3>
                                        <p class="mt-1 line-clamp-2 text-[11px] leading-snug text-stone-500">
                                            {{ $menuDesc }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between px-1.5 pb-1 pt-3">
                                    <span class="text-[13px] font-extrabold">Rp
                                        {{ number_format($menuPrice, 0, ',', '.') }}</span>
                                    <button type="button"
                                        class="order-btn grid h-9 w-9 place-items-center rounded-full bg-coffee text-white transition hover:bg-caramel disabled:cursor-not-allowed disabled:bg-stone-400 sm:h-7 sm:w-7"
                                        aria-label="{{ $isCafeOpen ? 'Tambah ' . $menuName . ' ke keranjang' : 'Maaf, cafe kami sedang tutup' }}"
                                        data-id="{{ $item['id'] ?? $index }}" data-name="{{ $menuName }}"
                                        data-price="{{ $menuPrice }}" data-image="{{ $imgSrc }}"
                                        @disabled(!$isCafeOpen)>
                                        <svg class="ic pointer-events-none h-3.5 w-3.5">
                                            <use href="#i-plus" />
                                        </svg>
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-stone-300 bg-white py-16 text-center">
                    <svg class="ic mx-auto mb-3 h-8 w-8 text-caramel">
                        <use href="#i-coffee" />
                    </svg>
                    <p class="text-sm font-medium text-stone-500">Belum ada menu yang ditambahkan di database.</p>
                </div>
            @endif

            <div id="emptyFilter"
                class="mt-2 hidden rounded-3xl border border-dashed border-stone-300 bg-white py-14 text-center">
                <p class="text-sm font-medium text-stone-500">Belum ada menu di kategori ini.</p>
            </div>
        </div>
    </section>

    <!-- TENTANG + JAM BUKA + RESERVASI -->
    <section id="tentang" class="bg-paper px-5 pb-10 md:px-10">
        <div class="mx-auto grid max-w-7xl gap-5 lg:grid-cols-[1.3fr_1fr_1.1fr]">

            <div class="relative min-h-[300px] overflow-hidden rounded-2xl">
                <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=1000&q=80"
                    alt="Suasana kedai BrewNest" class="absolute inset-0 h-full w-full object-cover">
                <div
                    class="absolute bottom-5 left-5 grid h-24 w-24 place-items-center rounded-full bg-paper text-center shadow-lg">
                    <div>
                        <svg class="ic mx-auto h-6 w-6">
                            <use href="#i-coffee" />
                        </svg>
                        <p class="mt-1 text-[8px] font-bold uppercase leading-tight tracking-wide">Biji
                            Kopi<br>Kualitas Premium</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col justify-center rounded-2xl bg-sand p-8">
                <p class="mb-3 font-display text-sm italic text-caramel">— Tentang Kami —</p>
                <h2 class="font-display text-3xl font-semibold leading-tight">
                    Lebih dari sekadar kopi. Ini sebuah <span class="text-caramel">pengalaman.</span>
                </h2>
                <p class="mt-4 text-xs leading-relaxed text-stone-600">
                    Di BrewNest, kami percaya kopi mempertemukan orang. Ruang yang nyaman, sapaan ramah, dan menu yang
                    diracik dengan teliti membuat tempat ini pas untuk bersantai dan menikmati waktu.
                </p>
                <a href="#kontak"
                    class="mt-6 w-fit rounded-md bg-coffee px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-white transition hover:bg-brown">Kenali
                    Kami Lebih Dekat</a>
            </div>

            <div id="reservasi" class="flex flex-col gap-5 rounded-2xl bg-coffee p-6 text-white">
                <div>
                    <h3 class="mb-4 flex items-center gap-2 font-display text-lg font-semibold"><svg class="ic">
                            <use href="#i-clock" />
                        </svg> Jam Buka</h3>
                    <dl class="space-y-2.5 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="text-stone-300">Setiap hari</dt>
                            <dd class="font-semibold">{{ $openHours }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative mt-auto overflow-hidden rounded-xl bg-caramel p-5">
                    <h4 class="mb-2 flex items-center gap-2 font-display text-base font-semibold"><svg
                            class="ic h-4 w-4">
                            <use href="#i-calendar" />
                        </svg> Pesan Meja</h4>
                    <p class="max-w-[10rem] text-[11px] leading-snug text-white/90">Amankan tempatmu dan nikmati
                        pengalaman kopi yang spesial.</p>
                    <a href="tel:{{ $phoneHref }}"
                        class="mt-4 inline-flex items-center gap-2 rounded-md border border-white/70 px-4 py-2 text-[10px] font-bold uppercase tracking-wider transition hover:bg-white hover:text-coffee">
                        Reservasi Sekarang <svg class="ic h-3 w-3">
                            <use href="#i-arrow" />
                        </svg>
                    </a>
                    <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80"
                        alt="" class="absolute -bottom-4 -right-4 h-32 w-32 rounded-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <!-- NILAI KAMI -->
    <section class="bg-paper px-5 pb-12 md:px-10">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 rounded-2xl bg-sand/70 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-paper text-caramel"><svg
                        class="ic">
                        <use href="#i-leaf" />
                    </svg></span>
                <div>
                    <p class="text-[11px] font-bold uppercase">Bahan Berkelanjutan</p>
                    <p class="text-[11px] leading-snug text-stone-500">Kami peduli pada manusia dan bumi.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-paper text-caramel"><svg
                        class="ic">
                        <use href="#i-beans" />
                    </svg></span>
                <div>
                    <p class="text-[11px] font-bold uppercase">Sangrai Lokal</p>
                    <p class="text-[11px] leading-snug text-stone-500">Disangrai dalam batch kecil.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-paper text-caramel"><svg
                        class="ic">
                        <use href="#i-star" />
                    </svg></span>
                <div>
                    <p class="text-[11px] font-bold uppercase">Bahan Berkualitas</p>
                    <p class="text-[11px] leading-snug text-stone-500">Hanya yang terbaik untukmu.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-paper text-caramel"><svg
                        class="ic">
                        <use href="#i-heart" />
                    </svg></span>
                <div>
                    <p class="text-[11px] font-bold uppercase">Dibuat dengan Hati</p>
                    <p class="text-[11px] leading-snug text-stone-500">Setiap cangkir diracik dengan cinta.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- INSTAGRAM -->
    <section class="bg-paper px-5 pb-16 md:px-10">
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-lg font-semibold">Ikuti Kami <span
                        class="font-sans text-sm font-bold">@@brewnest.coffee</span></h3>
                <a href="https://brewnest.coffee/"
                    class="flex items-center gap-2 text-xs font-semibold hover:text-caramel">Lihat Semua
                    <svg class="ic h-4 w-4">
                        <use href="#i-arrow" />
                    </svg></a>
            </div>
            <div class="grid grid-cols-3 gap-3 md:grid-cols-6">
                <img loading="lazy" alt="" class="ig-img aspect-[4/3] w-full rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=500&q=75">
                <img loading="lazy" alt="" class="ig-img aspect-[4/3] w-full rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=500&q=75">
                <img loading="lazy" alt="" class="ig-img aspect-[4/3] w-full rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=500&q=75">
                <img loading="lazy" alt="" class="ig-img aspect-[4/3] w-full rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=500&q=75">
                <img loading="lazy" alt="" class="ig-img aspect-[4/3] w-full rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=500&q=75">
                <img loading="lazy" alt="" class="ig-img aspect-[4/3] w-full rounded-xl object-cover"
                    src="https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=500&q=75">
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer id="kontak" class="bg-coffee text-stone-300">
        <div
            class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-5 py-10 md:gap-10 md:px-10 lg:grid-cols-[1.3fr_1fr_1fr_1.2fr_1.5fr]">

            <div class="col-span-2 lg:col-span-1">
                <a href="#home" class="flex items-center gap-3 text-white">
                    <span class="grid h-10 w-10 place-items-center rounded-xl border border-white/60"><svg
                            class="ic h-6 w-6">
                            <use href="#i-coffee" />
                        </svg></span>
                    <span>
                        <span class="block font-display text-2xl font-semibold leading-none">BrewNest</span>
                        <span class="mt-1 block text-[10px] font-semibold tracking-[2.5px] text-stone-400">COFFEE &amp;
                            EATERY</span>
                    </span>
                </a>
                <p class="mt-4 max-w-[16rem] text-xs leading-relaxed">{{ $storeTagline }}</p>
                <div class="mt-5 flex gap-2">
                    <a href="https://www.facebook.com/brewnestphilly" aria-label="Facebook"
                        class="grid h-8 w-8 place-items-center rounded-full border border-white/30 transition hover:border-tan hover:text-tan"><svg
                            class="ic h-3.5 w-3.5">
                            <use href="#i-facebook" />
                        </svg></a>
                    <a href="https://www.instagram.com/brewnestphilly" aria-label="Instagram"
                        class="grid h-8 w-8 place-items-center rounded-full border border-white/30 transition hover:border-tan hover:text-tan"><svg
                            class="ic h-3.5 w-3.5">
                            <use href="#i-instagram" />
                        </svg></a>
                    <a href="https://x.com/brewnestphilly" aria-label="Twitter"
                        class="grid h-8 w-8 place-items-center rounded-full border border-white/30 transition hover:border-tan hover:text-tan"><svg
                            class="ic h-3.5 w-3.5">
                            <use href="#i-twitter" />
                        </svg></a>
                </div>
            </div>

            <div>
                <h4 class="mb-4 text-xs font-bold text-white">Tautan Cepat</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#home" class="hover:text-tan">Beranda</a></li>
                    <li><a href="#menu" class="hover:text-tan">Menu</a></li>
                    <li><a href="#tentang" class="hover:text-tan">Tentang Kami</a></li>
                    <li><a href="#reservasi" class="hover:text-tan">Reservasi</a></li>
                    <li>
                        <a href="{{ $isAdmin ? route('admin.dashboard') : route('login') }}"
                            class="hover:text-tan">{{ $isAdmin ? 'Dashboard Admin' : 'Login Admin' }}</a>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="mb-4 text-xs font-bold text-white">Menu Kami</h4>
                <ul class="space-y-2 text-xs">
                    @forelse (array_slice($categories, 0, 5) as $cat)
                        @php
                            $fName = $cat['name'] ?? ($cat['nama_kategori'] ?? '');
                            $fId = $cat['id'] ?? $fName;
                        @endphp
                        <li><a href="#menu" class="footer-cat hover:text-tan"
                                data-category="{{ $fId }}">{{ $fName }}</a></li>
                    @empty
                        <li><a href="#menu" class="hover:text-tan">Semua menu</a></li>
                    @endforelse
                </ul>
            </div>

            <div class="col-span-2 lg:col-span-1">
                <h4 class="mb-4 text-xs font-bold text-white">Hubungi Kami</h4>

                @php
                    // Nomor WhatsApp: 0857... -> 62857...
                    $waNumber = preg_replace('/\D/', '', (string) $storePhone);
                    if (str_starts_with($waNumber, '0')) {
                        $waNumber = '62' . substr($waNumber, 1);
                    }
                @endphp

                <ul class="space-y-3 text-xs">
                    <li class="flex gap-2"><svg class="ic mt-0.5 h-3.5 w-3.5">
                            <use href="#i-pin" />
                        </svg><span>{{ $storeAddress }}</span></li>
                    <li class="flex gap-2"><svg class="ic mt-0.5 h-3.5 w-3.5">
                            <use href="#i-phone" />
                        </svg><a
                            href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Halo ' . $storeName . ', saya mau tanya...') }}"
                            target="_blank" rel="noopener" class="hover:text-tan">{{ $storePhone }}</a></li>
                    <li class="flex gap-2"><svg class="ic mt-0.5 h-3.5 w-3.5">
                            <use href="#i-mail" />
                        </svg><a href="mailto:{{ $storeEmail }}" class="hover:text-tan">{{ $storeEmail }}</a>
                    </li>
                    <li class="flex gap-2"><svg class="ic mt-0.5 h-3.5 w-3.5">
                            <use href="#i-clock" />
                        </svg><span>{{ $openHours }}</span></li>
                </ul>
            </div>

            <!-- Peta lokasi (menggantikan logo) -->
            <div class="col-span-2 flex flex-col items-start lg:col-span-1 lg:items-end">
                <div
                    class="relative h-44 w-full overflow-hidden rounded-2xl bg-paper shadow-lg shadow-black/30 ring-1 ring-caramel/40 lg:max-w-[22rem]">
                    <iframe title="Lokasi {{ $storeName }}" src="{{ $mapEmbedUrl }}"
                        class="h-full w-full border-0" loading="lazy" allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"></iframe>

                    <a href="{{ $mapOpenUrl }}" target="_blank" rel="noopener"
                        class="absolute left-2 top-2 rounded-md bg-white px-3 py-1.5 text-[11px] font-semibold text-blue-600 shadow transition hover:bg-stone-100">
                        Buka di Maps ↗
                    </a>
                </div>
                <p class="mt-3 font-display text-sm italic text-tan lg:text-right">Good Coffee, Good Vibes</p>
            </div>
        </div>

        <div class="border-t border-white/10 py-4 text-center text-[11px] text-stone-400">
            © {{ date('Y') }} {{ $storeName }} &amp; Eatery. Hak cipta dilindungi.
        </div>
    </footer>

    <!-- MODAL KERANJANG (HP: bottom sheet) -->
    <div id="cartModal"
        class="fixed inset-0 z-[60] hidden items-end justify-center bg-black/60 p-0 backdrop-blur-sm sm:items-center sm:p-4"
        role="dialog" aria-modal="true" aria-labelledby="cartTitle">
        <div
            class="relative flex max-h-[92dvh] w-full max-w-xl flex-col overflow-y-auto rounded-t-3xl bg-paper p-5 shadow-2xl sm:max-h-[90vh] sm:rounded-3xl sm:p-6">
            <div class="mb-4 flex items-center justify-between border-b border-stone-200 pb-3">
                <h3 id="cartTitle" class="flex items-center gap-2 font-display text-xl font-semibold"><svg
                        class="ic">
                        <use href="#i-cart" />
                    </svg> Keranjang Pesanan</h3>
                <button type="button" id="cartClose" class="text-xl font-bold text-stone-400 hover:text-coffee"
                    aria-label="Tutup keranjang">✕</button>
            </div>

            <div id="cartItemsList"
                class="max-h-52 min-h-[5rem] divide-y divide-stone-200 overflow-y-auto pr-1 sm:max-h-none sm:flex-1">
                <p class="py-8 text-center text-xs italic text-stone-400">Keranjang kamu masih kosong.</p>
            </div>

            <div class="mt-4 space-y-3 border-t border-stone-200 pt-4">
                <div class="flex items-center justify-between text-sm font-semibold">
                    <span class="text-stone-600">Subtotal</span><span id="cartSubtotal">Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-sm font-semibold">
                    <span class="text-stone-600">Pajak ({{ $taxLabel }}%)</span><span id="cartTax">Rp 0</span>
                </div>
                <div class="flex items-center justify-between border-t border-stone-200 pt-2 text-base font-extrabold">
                    <span>Total</span><span id="cartTotalPrice" class="text-lg text-caramel">Rp 0</span>
                </div>

                <div class="space-y-2 pt-2">
                    <input type="text" id="customerName" placeholder="Nama Pemesan"
                        class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-base focus:border-caramel focus:outline-none sm:text-xs">
                    <input type="text" id="customerTable" placeholder="Nomor Meja / Alamat Pengiriman"
                        class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-base focus:border-caramel focus:outline-none sm:text-xs">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" id="cartContinue"
                        class="flex-1 rounded-xl bg-sand py-3 text-xs font-bold text-stone-700 transition hover:bg-stone-300">Lanjut
                        Belanja</button>
                    <button type="button" id="cartCheckout"
                        class="flex-1 rounded-xl bg-coffee py-3 text-xs font-bold text-white transition hover:bg-brown">Lanjut
                        ke Kasir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="toast"
        class="pointer-events-none fixed bottom-6 left-1/2 z-[70] w-max max-w-[90vw] -translate-x-1/2 rounded-full bg-coffee px-5 py-3 text-center text-xs font-bold text-white opacity-0 shadow-xl transition-opacity duration-300"
        role="status" aria-live="polite"></div>

    <!-- SCRIPT -->
    <script>
        const CART_KEY = 'brewNestCart';
        // Persentase pajak diambil dari Admin > Settings
        const TAX_RATE = @json($taxPercent / 100);
        const FALLBACK_IMG = 'https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=800&q=80';

        // Gambar gagal dimuat -> ganti dengan gambar cadangan (sekali saja)
        document.addEventListener('error', e => {
            const t = e.target;
            if (t.tagName === 'IMG' && !t.dataset.fb) {
                t.dataset.fb = '1';
                t.src = FALLBACK_IMG;
            }
        }, true);

        function loadCart() {
            try {
                const data = JSON.parse(localStorage.getItem(CART_KEY));
                if (!Array.isArray(data)) return [];
                return data
                    .filter(i => i && i.id != null && Number(i.price) >= 0 && Number(i.qty) > 0)
                    .map(i => ({
                        id: String(i.id),
                        name: String(i.name ?? ''),
                        price: Number(i.price),
                        image: String(i.image ?? ''),
                        qty: Math.floor(Number(i.qty))
                    }));
            } catch (e) {
                return [];
            }
        }

        function saveCart() {
            try {
                localStorage.setItem(CART_KEY, JSON.stringify(cart));
            } catch (e) {
                showToast('Keranjang tidak bisa disimpan di browser ini.');
            }
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

        let cart = loadCart();
        let toastTimer;

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.remove('opacity-0');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.add('opacity-0'), 2000);
        }

        function updateCartCount() {
            const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
            document.querySelectorAll('.cart-count').forEach(el => el.textContent = totalQty);
        }

        /* Menu mobile */
        const mobileMenu = document.getElementById('mobileMenu');
        const menuToggle = document.getElementById('menuToggle');
        const menuIcon = menuToggle.querySelector('use');

        function setMobileMenu(open) {
            mobileMenu.classList.toggle('hidden', !open);
            mobileMenu.classList.toggle('flex', open);
            menuToggle.setAttribute('aria-expanded', String(open));
            menuToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
            menuIcon.setAttribute('href', open ? '#i-close' : '#i-menu');
        }
        menuToggle.addEventListener('click', () => setMobileMenu(mobileMenu.classList.contains('hidden')));
        mobileMenu.querySelectorAll('a[href^="#"]').forEach(a => a.addEventListener('click', () => setMobileMenu(false)));

        // Tutup menu saat klik di luar, tekan Esc, atau layar melebar ke desktop
        document.addEventListener('click', e => {
            if (mobileMenu.classList.contains('hidden')) return;
            if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) setMobileMenu(false);
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                setMobileMenu(false);
                menuToggle.focus();
            }
        });
        window.matchMedia('(min-width: 1024px)').addEventListener('change', e => {
            if (e.matches) setMobileMenu(false);
        });

        /* Scrollspy: tandai link navbar sesuai section yang sedang dilihat */
        const spyEls = ['home', 'menu', 'tentang', 'kontak'].map(id => document.getElementById(id)).filter(Boolean);
        const spyLinks = document.querySelectorAll('[data-spy]');
        let spyCurrent = '';

        function updateSpy() {
            if (!spyEls.length) return;
            const line = window.innerHeight * 0.35;
            let current = spyEls[0].id;
            spyEls.forEach(el => {
                if (el.getBoundingClientRect().top <= line) current = el.id;
            });
            // footer pendek: begitu mentok di bawah halaman, anggap "Kontak" aktif
            if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) current = 'kontak';
            if (current === spyCurrent) return;
            spyCurrent = current;
            spyLinks.forEach(l => l.classList.toggle('is-active', l.dataset.spy === current));
        }
        window.addEventListener('scroll', updateSpy, {
            passive: true
        });
        window.addEventListener('resize', updateSpy);
        updateSpy();

        /* Modal keranjang */
        const cartModal = document.getElementById('cartModal');
        const cafeIsOpen = @json($isCafeOpen);
        const cafeClosedMessage = 'Maaf, cafe kami sedang tutup. Silakan pesan kembali saat cafe sudah buka.';

        function openCartModal() {
            if (!cafeIsOpen) {
                showToast(cafeClosedMessage);
                return;
            }

            setMobileMenu(false);
            cartModal.classList.remove('hidden');
            cartModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            renderCart();
        }

        function closeCartModal() {
            cartModal.classList.add('hidden');
            cartModal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.getElementById('cartBtn').addEventListener('click', openCartModal);
        document.getElementById('cartBtnTop').addEventListener('click', openCartModal);
        document.getElementById('cartClose').addEventListener('click', closeCartModal);
        document.getElementById('cartContinue').addEventListener('click', closeCartModal);
        document.getElementById('cartCheckout').addEventListener('click', checkoutOrder);
        cartModal.addEventListener('click', e => {
            if (e.target === cartModal) closeCartModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !cartModal.classList.contains('hidden')) closeCartModal();
        });

        /* Tambah ke keranjang */
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.order-btn');
            if (!btn) return;

            if (!cafeIsOpen) {
                showToast(cafeClosedMessage);
                return;
            }

            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const price = Math.round(parseFloat(btn.dataset.price)) || 0;
            const image = btn.dataset.image;

            const existing = cart.find(item => item.id === id);
            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({
                    id,
                    name,
                    price,
                    image,
                    qty: 1
                });
            }

            saveCart();
            updateCartCount();
            showToast(name + ' ditambahkan ke keranjang');
        });

        /* Render keranjang */
        function renderCart() {
            const listContainer = document.getElementById('cartItemsList');

            if (cart.length === 0) {
                listContainer.innerHTML =
                    '<p class="py-8 text-center text-xs italic text-stone-400">Keranjang kamu masih kosong.</p>';
                document.getElementById('cartSubtotal').textContent = rupiah(0);
                document.getElementById('cartTax').textContent = rupiah(0);
                document.getElementById('cartTotalPrice').textContent = rupiah(0);
                return;
            }

            let html = '';
            let subtotal = 0;

            cart.forEach((item, index) => {
                const itemSubtotal = item.price * item.qty;
                subtotal += itemSubtotal;
                const safeName = escapeHtml(item.name);

                html += `
                    <div class="flex flex-col gap-2 py-3 text-xs sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <img src="${escapeHtml(item.image)}" class="h-12 w-12 shrink-0 rounded-xl border object-cover" alt="${safeName}">
                            <div class="min-w-0">
                                <p class="truncate font-bold">${safeName}</p>
                                <p class="text-stone-500">${rupiah(item.price)}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pl-[3.75rem] sm:pl-0">
                            <button type="button" onclick="changeQty(${index}, -1)" class="h-8 w-8 rounded-lg bg-sand font-bold hover:bg-stone-300 sm:h-6 sm:w-6" aria-label="Kurangi">-</button>
                            <span class="w-5 text-center font-bold">${item.qty}</span>
                            <button type="button" onclick="changeQty(${index}, 1)" class="h-8 w-8 rounded-lg bg-sand font-bold hover:bg-stone-300 sm:h-6 sm:w-6" aria-label="Tambah">+</button>
                            <span class="ml-auto font-bold text-caramel sm:ml-2">${rupiah(itemSubtotal)}</span>
                            <button type="button" onclick="removeItem(${index})" class="ml-1 hover:opacity-70" aria-label="Hapus">🗑️</button>
                        </div>
                    </div>
                `;
            });

            const tax = Math.round(subtotal * TAX_RATE);
            listContainer.innerHTML = html;
            document.getElementById('cartSubtotal').textContent = rupiah(subtotal);
            document.getElementById('cartTax').textContent = rupiah(tax);
            document.getElementById('cartTotalPrice').textContent = rupiah(subtotal + tax);
        }

        function changeQty(index, amount) {
            if (!cart[index]) return;
            cart[index].qty += amount;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            saveCart();
            updateCartCount();
            renderCart();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            saveCart();
            updateCartCount();
            renderCart();
        }

        function checkoutOrder() {
            if (!cafeIsOpen) {
                showToast(cafeClosedMessage);
                return;
            }

            const name = document.getElementById('customerName').value.trim();
            const table = document.getElementById('customerTable').value.trim();

            if (cart.length === 0) {
                showToast('Keranjang masih kosong.');
                return;
            }
            if (!name || !table) {
                showToast('Isi Nama Pemesan dan Nomor Meja/Alamat dulu.');
                return;
            }

            try {
                localStorage.setItem('customerName', name);
                localStorage.setItem('customerTable', table);
            } catch (e) {}
            saveCart();

            window.location.href = '{{ route('checkout.index') }}';
        }

        /* Filter kategori */
        const categoryBtns = document.querySelectorAll('.category-btn');
        const menuItems = document.querySelectorAll('.menu-item');
        const emptyFilter = document.getElementById('emptyFilter');
        const ACTIVE = ['bg-coffee', 'text-white', 'border-coffee'];
        const IDLE = ['bg-white', 'text-stone-600', 'border-stone-200'];

        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => {
                    b.classList.remove(...ACTIVE);
                    b.classList.add(...IDLE);
                });
                btn.classList.remove(...IDLE);
                btn.classList.add(...ACTIVE);

                const selected = String(btn.dataset.category).toLowerCase().trim();
                const selectedName = String(btn.dataset.categoryName || '').toLowerCase().trim();
                let visible = 0;

                menuItems.forEach(item => {
                    const cats = String(item.dataset.cats || '').split('|');
                    const show = selected === 'all' || cats.includes(selected) || (selectedName !==
                        '' && cats.includes(selectedName));
                    item.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                emptyFilter.classList.toggle('hidden', visible > 0 || menuItems.length === 0);
                const track = document.getElementById('menuTrack');
                if (track) track.scrollTo({
                    left: 0
                });
            });
        });

        // Link kategori di footer ikut memicu filter
        document.querySelectorAll('.footer-cat').forEach(link => {
            link.addEventListener('click', () => {
                const target = document.querySelector('.category-btn[data-category="' + link.dataset
                    .category + '"]');
                if (target) target.click();
            });
        });

        /* Panah carousel */
        const track = document.getElementById('menuTrack');
        if (track) {
            document.getElementById('menuPrev').addEventListener('click', () => track.scrollBy({
                left: -track.clientWidth * 0.8,
                behavior: 'smooth'
            }));
            document.getElementById('menuNext').addEventListener('click', () => track.scrollBy({
                left: track.clientWidth * 0.8,
                behavior: 'smooth'
            }));
        }

        updateCartCount();
    </script>

    <!-- ===================== ANIMASI (JS) ===================== -->
    <script>
        (function() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            /* 1. Scroll progress bar + navbar shadow + parallax hero (desktop saja) */
            const bar = document.createElement('div');
            bar.id = 'scrollBar';
            document.body.appendChild(bar);

            const header = document.querySelector('header.sticky');
            const heroImgWrap = document.querySelector('#home > div:first-child');
            const isDesktop = window.matchMedia('(min-width: 1024px)');
            let ticking = false;

            function onScroll() {
                const y = window.scrollY;
                const max = document.documentElement.scrollHeight - window.innerHeight;
                bar.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
                if (header) header.classList.toggle('is-scrolled', y > 10);
                if (heroImgWrap && !reduce && isDesktop.matches && y < 900) {
                    heroImgWrap.style.transform = 'translateY(' + (y * 0.15) + 'px)';
                } else if (heroImgWrap) {
                    heroImgWrap.style.transform = '';
                }
                ticking = false;
            }
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(onScroll);
                    ticking = true;
                }
            }, {
                passive: true
            });
            onScroll();

            /* 2. Uap kopi + kilau pada tombol */
            const ctaMenu = document.querySelector('#home a[href="#menu"]');
            if (ctaMenu) {
                ctaMenu.classList.add('btn-shine');
                const steam = document.createElement('span');
                steam.className = 'steam-wrap';
                steam.style.cssText = 'position:absolute;left:0;right:0;top:0;pointer-events:none';
                steam.innerHTML = '<i></i><i></i><i></i>';
                ctaMenu.style.overflow = 'visible';
                ctaMenu.appendChild(steam);
            }
            document.querySelectorAll('#home a[href="#reservasi"], #cartCheckout').forEach(b => b.classList.add(
                'btn-shine'));

            /* 3. Scroll reveal (dipasang otomatis ke elemen) */
            const targets = [
                ['#menu .text-center > *', ''],
                ['#menu .category-btn', 'zoom'],
                ['#menu .menu-item', ''],
                ['#tentang .grid > :nth-child(1)', 'from-left'],
                ['#tentang .grid > :nth-child(2)', ''],
                ['#tentang .grid > :nth-child(3)', 'from-right'],
                ['section:not([id]) .grid > div', 'zoom'],
                ['section:not([id]) .mb-4', ''],
                ['footer .grid > div', '']
            ];

            const io = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
                entries.forEach(en => {
                    if (en.isIntersecting) {
                        en.target.classList.add('in');
                        io.unobserve(en.target);
                    }
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            }) : null;

            targets.forEach(([sel, variant]) => {
                document.querySelectorAll(sel).forEach((el, i) => {
                    el.classList.add('reveal');
                    if (variant) el.classList.add(variant);
                    el.style.setProperty('--d', (Math.min(i, 8) * 0.08) + 's');
                    io ? io.observe(el) : el.classList.add('in');
                });
            });

            /* 4. Animasi saat filter kategori berubah */
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    let n = 0;
                    document.querySelectorAll('.menu-item').forEach(item => {
                        if (item.style.display === 'none') return;
                        item.classList.add('in');
                        item.classList.remove('pop');
                        void item.offsetWidth; // restart animasi
                        item.style.setProperty('--d', (Math.min(n++, 8) * 0.06) + 's');
                        item.classList.add('pop');
                    });
                });
            });

            /* 5. Gambar terbang ke keranjang + badge membal */
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.order-btn');
                if (!btn) return;

                document.querySelectorAll('.cart-count').forEach(c => {
                    c.classList.remove('bump');
                    void c.offsetWidth;
                    c.classList.add('bump');
                });

                if (reduce) return;

                const card = btn.closest('.menu-item');
                const img = card && card.querySelector('img');
                const cartTarget = [document.getElementById('cartBtn'), document.getElementById('cartBtnTop')]
                    .find(el => el && el.offsetParent !== null);
                if (!img || !cartTarget) return;

                const from = img.getBoundingClientRect();
                const to = cartTarget.getBoundingClientRect();

                const fly = document.createElement('img');
                fly.src = img.src;
                fly.className = 'fly-img';
                fly.style.left = from.left + from.width / 2 - 40 + 'px';
                fly.style.top = from.top + from.height / 2 - 40 + 'px';
                fly.style.width = fly.style.height = '80px';
                document.body.appendChild(fly);

                const dx = (to.left + to.width / 2) - (from.left + from.width / 2);
                const dy = (to.top + to.height / 2) - (from.top + from.height / 2);

                const anim = fly.animate([{
                        transform: 'translate(0,0) scale(1)',
                        opacity: 1
                    },
                    {
                        transform: `translate(${dx * 0.5}px, ${dy * 0.5 - 90}px) scale(.7)`,
                        opacity: .95,
                        offset: .5
                    },
                    {
                        transform: `translate(${dx}px, ${dy}px) scale(.12)`,
                        opacity: .2
                    }
                ], {
                    duration: 800,
                    easing: 'cubic-bezier(.5, 0, .75, .4)'
                });
                anim.onfinish = () => fly.remove();
            });
        })();
    </script>

</body>

</html>
