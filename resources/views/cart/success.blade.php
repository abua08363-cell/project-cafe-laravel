@php
    // $order boleh berupa model Eloquent atau array
    $order = is_array($order) ? $order : (method_exists($order, 'toArray') ? $order->toArray() : (array) $order);

    $orderId = $order['id'] ?? null;
    $orderNumber = $order['order_number'] ?? ($order['id'] ?? '-');

    $reviewItems = collect($order_items ?? ($items ?? []))
        ->map(fn($i) => is_array($i) ? $i : (method_exists($i, 'toArray') ? $i->toArray() : (array) $i))
        ->values()
        ->all();

    $googleReviewUrl = $google_review_url ?? '';

    /* ---------- Helper struk ---------- */
    $rp = static fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    // URL foto menu: mendukung URL penuh, path "uploads/...", atau nama file di uploads/menus
    $imgUrl = static function (array $it): ?string {
        $f =
            $it['image'] ??
            ($it['photo'] ?? ($it['foto'] ?? ($it['gambar'] ?? ($it['image_url'] ?? ($it['menu_image'] ?? null)))));
        if (!$f) {
            return null;
        }
        if (preg_match('#^(https?:)?//#', $f) || str_starts_with($f, 'data:')) {
            return $f;
        }
        $f = ltrim($f, '/');
        return str_starts_with($f, 'uploads/') ? asset($f) : asset('uploads/menus/' . $f);
    };

    // Hitung subtotal dari item
    $calcSubtotal = 0;
    foreach ($reviewItems as $it) {
        $q = (int) ($it['qty'] ?? ($it['quantity'] ?? 1));
        $p = (float) ($it['price'] ?? ($it['harga'] ?? 0));
        $calcSubtotal += (float) ($it['subtotal'] ?? ($it['total'] ?? $p * $q));
    }
    $grandTotal = (float) ($order['total'] ?? ($order['grand_total'] ?? 0));
    $subtotal = (float) ($order['subtotal'] ?? ($calcSubtotal > 0 ? $calcSubtotal : $grandTotal));
    $tax = (float) ($order['tax'] ?? ($order['pajak'] ?? 0));
    $service = (float) ($order['service_fee'] ?? ($order['service'] ?? 0));
    $discount = (float) ($order['discount'] ?? ($order['diskon'] ?? 0));
    $paidAmount = (float) ($order['paid_amount'] ?? ($order['cash'] ?? 0));
    $change = $paidAmount > $grandTotal ? $paidAmount - $grandTotal : 0;
    $totalQty = 0;
    foreach ($reviewItems as $it) {
        $totalQty += (int) ($it['qty'] ?? ($it['quantity'] ?? 1));
    }

    $orderTime = !empty($order['created_at']) ? strtotime($order['created_at']) : time();
    $cafePhoto = $cafe_photo_url ?? null; // opsional: foto kafe untuk header struk

    // Status pembayaran nyata (bukan selalu "lunas")
    $isPaid = in_array(
        strtolower((string) ($order['payment_status'] ?? '')),
        ['paid', 'lunas', 'success', 'settlement', 'completed', 'complete', 'selesai', 'done'],
        true,
    );

    // Barcode sederhana dari nomor pesanan
    $barSeed = md5((string) $orderNumber);
    $bars = '';
    $x = 0;
    for ($i = 0; $i < 40; $i++) {
        $w = (hexdec($barSeed[$i % 32]) % 3) + 1;
        if ($i % 2 === 0) {
            $bars .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="48" fill="#1d110b"/>';
        }
        $x += $w + ((hexdec($barSeed[($i + 7) % 32]) % 2) + 1);
    }
    $barW = max($x, 1);
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($isPaid ? 'Pembayaran Berhasil - BrewNest Coffee' : 'Pesanan Diterima - BrewNest Coffee') }}
    </title>
    <link rel="icon" type="image/jpeg" href="{{ asset('kopi.jpeg') }}">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Courier+Prime:wght@400;700&display=swap"
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
                        sand: '#ece2d4'
                    },
                    fontFamily: {
                        display: ['"Playfair Display"', 'Georgia', 'serif'],
                        receipt: ['"Courier Prime"', '"Courier New"', 'monospace']
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
        }

        .bg-glow {
            background:
                radial-gradient(circle at 15% 10%, rgba(217, 164, 106, .28), transparent 40%),
                radial-gradient(circle at 90% 85%, rgba(117, 74, 48, .18), transparent 45%),
                #fbf8f3;
        }

        .blob {
            position: fixed;
            border-radius: 9999px;
            filter: blur(70px);
            opacity: .35;
            pointer-events: none;
            z-index: 0;
            animation: drift 14s ease-in-out infinite alternate;
        }

        @keyframes drift {
            to {
                transform: translate(40px, -30px) scale(1.15);
            }
        }

        .check-ring {
            stroke-dasharray: 189;
            stroke-dashoffset: 189;
            animation: draw .8s cubic-bezier(.6, .1, .2, 1) .2s forwards;
        }

        .check-mark {
            stroke-dasharray: 40;
            stroke-dashoffset: 40;
            animation: draw .5s ease-out .95s forwards;
        }

        @keyframes draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        .badge-pop {
            animation: badgePop .7s cubic-bezier(.3, 1.6, .5, 1) both;
        }

        @keyframes badgePop {
            from {
                transform: scale(0) rotate(-40deg);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        .pulse-ring {
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            border: 2px solid #10b981;
            animation: pulseOut 2.4s ease-out 1.2s infinite;
            opacity: 0;
        }

        .pulse-ring.r2 {
            animation-delay: 1.9s;
        }

        @keyframes pulseOut {
            0% {
                transform: scale(1);
                opacity: .6;
            }

            100% {
                transform: scale(1.9);
                opacity: 0;
            }
        }

        .rise {
            opacity: 0;
            animation: rise .7s cubic-bezier(.2, .8, .2, 1) forwards;
            animation-delay: var(--d, 0s);
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* ============ STRUK ============ */
        .receipt-wrap {
            animation: printOut 1.1s cubic-bezier(.2, .8, .2, 1) .5s both;
            transform-origin: top center;
        }

        @keyframes printOut {
            from {
                opacity: 0;
                transform: translateY(-24px) scaleY(.92);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .receipt {
            position: relative;
            background: #fffdf8;
            font-family: 'Courier Prime', 'Courier New', monospace;
            color: #1d110b;
            font-size: 13px;
            line-height: 1.45;
            box-shadow: 0 18px 40px -12px rgba(29, 17, 11, .25), 0 0 0 1px rgba(29, 17, 11, .05);
            /* tepi atas & bawah bergerigi seperti kertas sobek */
            --tooth: 10px;
            -webkit-mask:
                conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) bottom / calc(var(--tooth) * 2) 51% repeat-x,
                conic-gradient(from 135deg at top, #0000, #000 1deg 89deg, #0000 90deg) top / calc(var(--tooth) * 2) 51% repeat-x;
            mask:
                conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) bottom / calc(var(--tooth) * 2) 51% repeat-x,
                conic-gradient(from 135deg at top, #0000, #000 1deg 89deg, #0000 90deg) top / calc(var(--tooth) * 2) 51% repeat-x;
            padding: 28px 22px 30px;
        }

        .receipt-shadow {
            filter: drop-shadow(0 14px 18px rgba(29, 17, 11, .16));
        }

        .rc-dash {
            border-top: 2px dashed #b8aa98;
            margin: 12px 0;
        }

        .rc-dbl {
            border-top: 3px double #8a7b69;
            margin: 12px 0;
        }

        .rc-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .rc-muted {
            color: #7d6f60;
        }

        .rc-thumb {
            width: 52px;
            height: 52px;
            flex: none;
            border-radius: 6px;
            object-fit: cover;
            background: #ece2d4;
            filter: grayscale(.15) contrast(1.02);
            border: 1px solid #e0d4c3;
        }

        .rc-thumb-ph {
            width: 52px;
            height: 52px;
            flex: none;
            border-radius: 6px;
            background: #ece2d4;
            border: 1px dashed #c9b9a3;
            display: grid;
            place-items: center;
            font-size: 20px;
        }

        .rc-stamp {
            position: absolute;
            right: 14px;
            top: 150px;
            transform: rotate(-12deg);
            border: 3px solid #0d9a6b;
            color: #0d9a6b;
            padding: 2px 12px;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 3px;
            font-size: 20px;
            opacity: .8;
            mix-blend-mode: multiply;
            animation: stamp .5s cubic-bezier(.3, 1.8, .5, 1) 1.8s both;
        }

        /* Pesanan belum dibayar: stempel berwarna karamel */
        .rc-stamp.pending {
            border-color: #b9854f;
            color: #b9854f;
            font-size: 15px;
            letter-spacing: 2px;
        }

        @keyframes stamp {
            from {
                transform: rotate(-12deg) scale(2.2);
                opacity: 0;
            }

            to {
                transform: rotate(-12deg) scale(1);
                opacity: .8;
            }
        }

        .progress-fill {
            width: 0;
            animation: fillBar 1.4s cubic-bezier(.6, .1, .2, 1) 1.6s forwards;
        }

        @keyframes fillBar {
            to {
                width: 50%;
            }
        }

        .step-dot-now {
            animation: dotPulse 1.6s ease-out 2.8s infinite;
        }

        @keyframes dotPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(185, 133, 79, .6);
            }

            100% {
                box-shadow: 0 0 0 12px rgba(185, 133, 79, 0);
            }
        }

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
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, .4), transparent);
            transform: skewX(-20deg);
            animation: shine 3.2s ease-in-out 3s infinite;
        }

        @keyframes shine {
            0% {
                left: -80%;
            }

            40%,
            100% {
                left: 140%;
            }
        }

        /* RATING & ULASAN */
        .star-btn {
            color: #d9d0c3;
            line-height: 0;
            border-radius: 8px;
            transition: color .15s, transform .15s;
        }

        .star-btn:focus-visible {
            outline: 2px solid #b9854f;
            outline-offset: 2px;
        }

        .star-btn.on {
            color: #f2a516;
        }

        .star-btn.hover {
            color: #f7c04d;
        }

        .star-btn:hover {
            transform: scale(1.12);
        }

        .chip {
            border: 1px solid #e3d9ca;
            background: #fff;
            transition: background .15s, border-color .15s, color .15s;
        }

        .chip:hover {
            border-color: #b9854f;
        }

        .chip[aria-pressed="true"] {
            background: #1d110b;
            border-color: #1d110b;
            color: #fff;
        }

        .seg button[aria-pressed="true"] {
            background: #1d110b;
            color: #fff;
        }

        .switch {
            width: 40px;
            height: 24px;
            border-radius: 9999px;
            background: #d9d0c3;
            position: relative;
            transition: background .2s;
            flex: none;
        }

        .switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: #fff;
            transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
        }

        .switch[aria-checked="true"] {
            background: #10b981;
        }

        .switch[aria-checked="true"]::after {
            transform: translateX(16px);
        }

        .reveal {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .4s ease;
        }

        .reveal.open {
            grid-template-rows: 1fr;
        }

        .reveal>div {
            overflow: hidden;
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-delay: 0s !important;
            }
        }

        @media print {

            .no-print,
            .blob {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .receipt-shadow {
                filter: none;
            }

            .receipt-wrap {
                animation: none;
            }
        }
    </style>
</head>

<body class="bg-glow min-h-screen text-coffee antialiased">

    <div class="blob left-[-80px] top-[20%] h-72 w-72 bg-tan"></div>
    <div class="blob bottom-[-60px] right-[-60px] h-80 w-80 bg-emerald-300" style="animation-delay:-6s"></div>

    <!-- Header -->
    <header class="no-print relative z-10 border-b border-stone-200/70 bg-paper/80 backdrop-blur">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-5 py-3.5">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl border border-coffee/70">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 2v2M14 2v2M6 2v2" />
                        <path
                            d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1" />
                    </svg>
                </span>
                <span>
                    <span class="block font-display text-2xl font-semibold leading-none">BrewNest</span>
                    <span class="mt-1 block text-[10px] font-semibold tracking-[2.5px] text-stone-500">COFFEE &amp;
                        EATERY</span>
                </span>
            </a>
            <a href="{{ url('/') }}" class="text-xs font-semibold text-brown transition hover:text-caramel">←
                Beranda</a>
        </div>
    </header>

    <main class="relative z-10 mx-auto max-w-xl px-4 pb-20 pt-10 sm:pt-14">

        <!-- Ikon Sukses -->
        <div class="no-print relative mx-auto mb-6 grid h-28 w-28 place-items-center">
            <span class="pulse-ring"></span>
            <span class="pulse-ring r2"></span>
            <div
                class="badge-pop grid h-28 w-28 place-items-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-xl shadow-emerald-500/30">
                <svg viewBox="0 0 80 80" class="h-16 w-16" fill="none" stroke="#fff" stroke-width="5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle class="check-ring" cx="40" cy="40" r="30" stroke-opacity=".45" />
                    <path class="check-mark" d="M27 41l9 9 18-20" />
                </svg>
            </div>
        </div>

        <div class="no-print mb-9 text-center">
            <h1 class="rise font-display text-4xl font-semibold sm:text-5xl" style="--d:.9s">
                @if ($isPaid)
                    Pembayaran <span class="italic text-caramel">Berhasil!</span>
                @else
                    Pesanan <span class="italic text-caramel">Diterima!</span>
                @endif
            </h1>
            <p class="rise mx-auto mt-3 max-w-sm text-sm leading-relaxed text-stone-600" style="--d:1.05s">Terima kasih
                sudah memesan di BrewNest. Barista kami sedang menyiapkan pesananmu dengan sepenuh hati.</p>
        </div>

        <!-- ================= STRUK ================= -->
        <div class="receipt-wrap receipt-shadow mx-auto max-w-[380px]">
            <div class="receipt">

                <!-- Kop struk -->
                <div class="text-center">
                    @if ($cafePhoto)
                        <img src="{{ $cafePhoto }}" alt="BrewNest"
                            class="mx-auto mb-3 h-20 w-full rounded-md object-cover" style="filter:grayscale(.3)"
                            onerror="this.remove()">
                    @endif
                    <svg class="mx-auto h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 2v2M14 2v2M6 2v2" />
                        <path
                            d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1" />
                    </svg>
                    <p class="mt-1 font-display text-2xl font-bold leading-none tracking-wide">BrewNest</p>
                    <p class="mt-1 text-[11px] tracking-[3px]">COFFEE &amp; EATERY</p>
                    <p class="rc-muted mt-2 text-[11px]">
                        {{ $cafe_address ?? 'Jl. Contoh No. 12, Jakarta' }}<br>
                        Telp. {{ $cafe_phone ?? '0812-3456-7890' }}
                    </p>
                </div>

                <div class="rc-dash"></div>

                <!-- Info transaksi -->
                <div class="space-y-0.5 text-[12px]">
                    <div class="rc-row"><span class="rc-muted">No. Pesanan</span><span
                            class="font-bold">#{{ $orderNumber }}</span></div>
                    <div class="rc-row"><span
                            class="rc-muted">Tanggal</span><span>{{ date('d/m/Y', $orderTime) }}</span></div>
                    <div class="rc-row"><span class="rc-muted">Jam</span><span>{{ date('H:i', $orderTime) }} WIB</span>
                    </div>
                    <div class="rc-row"><span class="rc-muted">Pemesan</span><span
                            class="text-right">{{ $order['customer_name'] ?? ($order['name'] ?? 'Tamu') }}</span></div>
                    <div class="rc-row"><span class="rc-muted">Meja/Alamat</span><span
                            class="text-right">{{ $order['table_number'] ?? ($order['table'] ?? '-') }}</span></div>
                    @if (!empty($order['cashier']))
                        <div class="rc-row"><span class="rc-muted">Kasir</span><span>{{ $order['cashier'] }}</span>
                        </div>
                    @endif
                </div>

                <div class="rc-dash"></div>

                <!-- Daftar item + foto -->
                <div class="space-y-3.5">
                    @forelse ($reviewItems as $it)
                        @php
                            $qty = (int) ($it['qty'] ?? ($it['quantity'] ?? 1));
                            $price = (float) ($it['price'] ?? ($it['harga'] ?? 0));
                            $line = (float) ($it['subtotal'] ?? ($it['total'] ?? $price * $qty));
                            $name = $it['name'] ?? ($it['menu_name'] ?? 'Menu');
                            $note = $it['note'] ?? ($it['notes'] ?? '');
                            $photo = $imgUrl($it);
                        @endphp
                        <div class="flex items-start gap-3">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="{{ $name }}" class="rc-thumb"
                                    loading="lazy"
                                    onerror="this.outerHTML='<div class=&quot;rc-thumb-ph&quot;>☕</div>'">
                            @else
                                <div class="rc-thumb-ph">☕</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-bold leading-tight">{{ $name }}</p>
                                @if ($note)
                                    <p class="rc-muted text-[11px] italic">* {{ $note }}</p>
                                @endif
                                <div class="rc-row mt-1 text-[12px]">
                                    <span class="rc-muted">{{ $qty }} x
                                        {{ number_format($price, 0, ',', '.') }}</span>
                                    <span class="font-bold">{{ number_format($line, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="rc-muted py-3 text-center text-[12px]">Rincian item tidak tersedia.</p>
                    @endforelse
                </div>

                <div class="rc-dash"></div>

                <!-- Rincian harga -->
                <div class="space-y-1 text-[12px]">
                    <div class="rc-row"><span class="rc-muted">Subtotal ({{ $totalQty }}
                            item)</span><span>{{ $rp($subtotal) }}</span></div>
                    @if ($discount > 0)
                        <div class="rc-row"><span class="rc-muted">Diskon</span><span>- {{ $rp($discount) }}</span>
                        </div>
                    @endif
                    @if ($service > 0)
                        <div class="rc-row"><span class="rc-muted">Biaya
                                layanan</span><span>{{ $rp($service) }}</span></div>
                    @endif
                    @if ($tax > 0)
                        <div class="rc-row"><span class="rc-muted">Pajak</span><span>{{ $rp($tax) }}</span></div>
                    @endif
                </div>

                <div class="rc-dbl"></div>

                <div class="rc-row items-baseline">
                    <span class="text-sm font-bold tracking-wider">TOTAL</span>
                    <span class="text-xl font-bold" id="totalNum"
                        data-total="{{ $grandTotal }}">{{ $rp($grandTotal) }}</span>
                </div>

                <div class="mt-2 space-y-1 text-[12px]">
                    <div class="rc-row"><span class="rc-muted">Metode bayar</span><span
                            class="font-bold uppercase">{{ $order['payment_method'] ?? 'Tunai' }}</span></div>
                    @if ($paidAmount > 0)
                        <div class="rc-row"><span class="rc-muted">Dibayar</span><span>{{ $rp($paidAmount) }}</span>
                        </div>
                        <div class="rc-row"><span class="rc-muted">Kembali</span><span>{{ $rp($change) }}</span>
                        </div>
                    @endif
                    <div class="rc-row"><span class="rc-muted">Status</span><span
                            class="font-bold">{{ $isPaid ? 'LUNAS' : 'BELUM DIBAYAR' }}</span></div>
                </div>

                <div class="rc-stamp {{ $isPaid ? '' : 'pending' }}">{{ $isPaid ? 'LUNAS' : 'MENUNGGU' }}</div>

                <div class="rc-dash"></div>

                <!-- Barcode -->
                <div class="text-center">
                    <svg class="mx-auto h-12 w-full max-w-[240px]" viewBox="0 0 {{ $barW }} 48"
                        preserveAspectRatio="none" aria-hidden="true">{!! $bars !!}</svg>
                    <p class="mt-1 text-[11px] tracking-[4px]">{{ $orderNumber }}</p>
                </div>

                <div class="rc-dash"></div>

                <div class="text-center text-[11px] leading-relaxed">
                    <p class="font-bold">TERIMA KASIH</p>
                    <p class="rc-muted">Simpan struk ini sebagai bukti pembayaran.<br>Sampai jumpa lagi di BrewNest ☕
                    </p>
                </div>
            </div>
        </div>

        <!-- Status pesanan (di luar struk) -->
        <div class="no-print rise mx-auto mt-8 max-w-[380px] rounded-2xl bg-white p-4 ring-1 ring-stone-200/70"
            style="--d:1.9s">
            <p class="mb-4 text-[11px] font-bold text-stone-500">Status pesanan</p>
            <div class="relative px-3">
                <div class="absolute left-3 right-3 top-[13px] h-1 rounded-full bg-stone-200"></div>
                <div class="absolute left-3 top-[13px] h-1 rounded-full" style="right:calc(50% - .75rem + 0px)">
                    <div class="progress-fill h-full rounded-full bg-gradient-to-r from-emerald-500 to-caramel"
                        style="width:0"></div>
                </div>
                <div class="relative flex justify-between text-center">
                    <div class="w-16">
                        <span
                            class="mx-auto grid h-7 w-7 place-items-center rounded-full bg-emerald-500 text-xs font-bold text-white">✓</span>
                        <p class="mt-2 text-[10px] font-bold">Diterima</p>
                    </div>
                    <div class="w-16">
                        <span
                            class="step-dot-now mx-auto grid h-7 w-7 place-items-center rounded-full bg-caramel text-xs text-white">☕</span>
                        <p class="mt-2 text-[10px] font-bold">Diproses</p>
                    </div>
                    <div class="w-16">
                        <span
                            class="mx-auto grid h-7 w-7 place-items-center rounded-full bg-stone-200 text-xs text-stone-400">🍽️</span>
                        <p class="mt-2 text-[10px] font-semibold text-stone-400">Siap</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Rating & Ulasan -->
        <section id="reviewCard" class="no-print rise mt-12" style="--d:2.1s" aria-labelledby="reviewTitle">
            <div class="rounded-3xl bg-white p-6 shadow-xl shadow-coffee/10 ring-1 ring-stone-200/70 sm:p-7">
                <div id="reviewForm" class="@if ($existingReview) hidden @endif">
                    <h2 id="reviewTitle" class="font-display text-2xl font-semibold">Bagaimana pesananmu?</h2>
                    <p class="mt-1 text-sm text-stone-500">Ulasanmu membantu kami meracik kopi yang lebih baik.</p>

                    <div class="mt-6 rounded-2xl bg-paper px-4 py-5 text-center ring-1 ring-stone-200/70">
                        <div id="overallStars" class="flex justify-center gap-1.5" role="radiogroup"
                            aria-label="Rating keseluruhan" data-size="40"></div>
                        <p id="overallLabel" class="mt-3 h-5 text-sm font-semibold text-brown" aria-live="polite">
                            Ketuk bintang untuk menilai</p>
                    </div>

                    <div id="reviewMore" class="reveal">
                        <div>
                            <div class="space-y-6 pt-6">
                                <div>
                                    <p class="text-sm font-bold">Nilai per aspek <span
                                            class="font-normal text-stone-400">(opsional)</span></p>
                                    <div class="mt-3 space-y-2.5">
                                        @php
                                            $aspects = [
                                                'rasa' => 'Rasa makanan & minuman',
                                                'pelayanan' => 'Keramahan pelayanan',
                                                'kecepatan' => 'Kecepatan penyajian',
                                                'suasana' => 'Kenyamanan tempat',
                                            ];
                                        @endphp
                                        @foreach ($aspects as $key => $label)
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-xs text-stone-600">{{ $label }}</span>
                                                <div class="flex gap-0.5" role="radiogroup"
                                                    aria-label="{{ $label }}"
                                                    data-aspect="{{ $key }}" data-size="22"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if (!empty($reviewItems))
                                    <div>
                                        <p class="text-sm font-bold">Menu yang kamu pesan <span
                                                class="font-normal text-stone-400">(opsional)</span></p>
                                        <div class="mt-3 space-y-2.5">
                                            @foreach ($reviewItems as $it)
                                                @php
                                                    $iid = $it['menu_id'] ?? ($it['id'] ?? null);
                                                    $iname = $it['name'] ?? ($it['menu_name'] ?? 'Menu');
                                                @endphp
                                                @continue($iid === null)
                                                <div class="flex items-center justify-between gap-3">
                                                    <span
                                                        class="truncate text-xs text-stone-600">{{ $iname }}</span>
                                                    <div class="flex flex-none gap-0.5" role="radiogroup"
                                                        aria-label="Rating {{ $iname }}"
                                                        data-item="{{ $iid }}" data-size="22"></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <p id="tagTitle" class="text-sm font-bold">Apa yang paling berkesan?</p>
                                    <div id="tagList" class="mt-3 flex flex-wrap gap-2"></div>
                                </div>

                                <div>
                                    <label for="reviewText" class="text-sm font-bold">Ceritakan pengalamanmu <span
                                            class="font-normal text-stone-400">(opsional)</span></label>
                                    <textarea id="reviewText" rows="4" maxlength="300"
                                        placeholder="Misalnya: latte-nya pas, tapi menunggu agak lama."
                                        class="mt-2 w-full resize-none rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm placeholder:text-stone-400 focus:border-caramel focus:outline-none focus:ring-2 focus:ring-caramel/30"></textarea>
                                    <p class="mt-1 text-right text-[11px] text-stone-400"><span
                                            id="charCount">0</span>/300</p>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-sm font-bold">Mau ajak teman ke sini?</p>
                                    <div class="seg flex overflow-hidden rounded-full border border-stone-300 text-xs font-semibold"
                                        id="recommendSeg">
                                        <button type="button" data-val="1" aria-pressed="false"
                                            class="px-4 py-2 transition">Pasti</button>
                                        <button type="button" data-val="0" aria-pressed="false"
                                            class="border-l border-stone-300 px-4 py-2 transition">Belum dulu</button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p id="anonLabel" class="text-sm font-bold">Kirim sebagai anonim</p>
                                        <p class="text-xs text-stone-500">Namamu tidak ditampilkan di ulasan.</p>
                                    </div>
                                    <button type="button" id="anonSwitch" class="switch" role="switch"
                                        aria-checked="false" aria-labelledby="anonLabel"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p id="reviewError"
                        class="mt-4 hidden rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-700"
                        role="alert"></p>

                    <button type="button" id="submitReview" disabled
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-coffee px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-coffee/20 transition hover:bg-brown disabled:cursor-not-allowed disabled:bg-stone-300 disabled:shadow-none">
                        <span id="submitText">Kirim ulasan</span>
                    </button>
                </div>

                <div id="reviewThanks" class="text-center @if (!$existingReview) hidden @endif">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-100 text-2xl">☕</div>
                    <h2 class="mt-4 font-display text-2xl font-semibold">Terima kasih atas ulasanmu!</h2>
                    <div id="thanksStars" class="mt-3 flex justify-center gap-1" aria-hidden="true">
                        @if ($existingReview)
                            {{ str_repeat('⭐', (int) $existingReview->rating) }}
                        @endif
                    </div>
                    <p id="thanksMsg" class="mx-auto mt-3 max-w-xs text-sm leading-relaxed text-stone-600">
                        @if ($existingReview)
                            Ulasan untuk pesanan ini sudah tersimpan.
                        @endif
                    </p>

                    <div id="thanksActions" class="mt-5 flex flex-col gap-2.5 sm:flex-row sm:justify-center">
                        @if ($googleReviewUrl)
                            <a id="googleBtn" href="{{ $googleReviewUrl }}" target="_blank" rel="noopener"
                                class="hidden items-center justify-center rounded-xl border border-stone-300 bg-white px-5 py-3 text-xs font-bold transition hover:border-coffee">
                                Tulis juga di Google Maps
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="no-print mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ url('/') }}"
                class="btn-shine flex-1 rounded-2xl bg-coffee px-6 py-4 text-center text-sm font-bold text-white shadow-xl shadow-coffee/20 transition hover:bg-brown">
                Kembali ke Beranda →
            </a>
            <button type="button" onclick="window.print()"
                class="rounded-2xl border border-stone-300 bg-white px-6 py-4 text-center text-sm font-bold text-stone-700 shadow-sm transition hover:bg-stone-50">
                Cetak Struk
            </button>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const REVIEW_URL = '{{ route('checkout.review', ['order' => $orderId]) }}';
            const CSRF = document.querySelector('meta[name="csrf-token"]').content;

            const overallStarsContainer = document.getElementById('overallStars');
            const overallLabel = document.getElementById('overallLabel');
            const reviewMore = document.getElementById('reviewMore');
            const submitBtn = document.getElementById('submitReview');
            const reviewText = document.getElementById('reviewText');
            const charCount = document.getElementById('charCount');
            const tagList = document.getElementById('tagList');
            const tagTitle = document.getElementById('tagTitle');
            const recommendSeg = document.getElementById('recommendSeg');
            const anonSwitch = document.getElementById('anonSwitch');
            const reviewForm = document.getElementById('reviewForm');
            const reviewThanks = document.getElementById('reviewThanks');
            const reviewError = document.getElementById('reviewError');

            let currentRating = 0;
            let recommendVal = null;
            let isAnon = false;

            const labels = ['', 'Kurang sekali', 'Kurang', 'Cukup', 'Puas', 'Sangat puas!'];
            const positiveTags = ['Rasa mantap', 'Barista ramah', 'Penyajian cepat', 'Tempat nyaman',
                'Harga sepadan'
            ];
            const negativeTags = ['Rasa kurang pas', 'Penyajian lama', 'Pelayanan kurang ramah',
                'Tempat kurang nyaman', 'Harga kurang sepadan'
            ];

            let selectedTags = [];

            if (overallStarsContainer) {
                const size = overallStarsContainer.dataset.size || 40;
                for (let i = 1; i <= 5; i++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'star-btn p-1';
                    btn.setAttribute('role', 'radio');
                    btn.setAttribute('aria-checked', 'false');
                    btn.setAttribute('aria-label', `${i} bintang`);
                    btn.innerHTML =
                        `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>`;

                    btn.addEventListener('click', () => {
                        currentRating = i;
                        updateOverallStars();
                        overallLabel.textContent = labels[i];
                        reviewMore.classList.add('open');
                        submitBtn.removeAttribute('disabled');
                        updateTags();
                    });

                    overallStarsContainer.appendChild(btn);
                }
            }

            function updateOverallStars() {
                const buttons = overallStarsContainer.querySelectorAll('.star-btn');
                buttons.forEach((btn, idx) => {
                    if (idx < currentRating) {
                        btn.classList.add('on');
                        btn.setAttribute('aria-checked', 'true');
                    } else {
                        btn.classList.remove('on');
                        btn.setAttribute('aria-checked', 'false');
                    }
                });
            }

            function updateTags() {
                tagList.innerHTML = '';
                selectedTags = [];
                const tagsToUse = currentRating >= 4 ? positiveTags : negativeTags;
                tagTitle.textContent = currentRating >= 4 ? 'Apa yang paling berkesan?' :
                    'Apa yang perlu ditingkatkan?';

                tagsToUse.forEach(tagText => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'chip rounded-full px-3.5 py-1.5 text-xs font-semibold';
                    chip.textContent = tagText;
                    chip.setAttribute('aria-pressed', 'false');

                    chip.addEventListener('click', () => {
                        const pressed = chip.getAttribute('aria-pressed') === 'true';
                        chip.setAttribute('aria-pressed', !pressed);
                        if (!pressed) {
                            selectedTags.push(tagText);
                        } else {
                            selectedTags = selectedTags.filter(t => t !== tagText);
                        }
                    });

                    tagList.appendChild(chip);
                });
            }

            document.querySelectorAll('[data-aspect], [data-item]').forEach(container => {
                const size = container.dataset.size || 22;
                let val = 0;
                for (let i = 1; i <= 5; i++) {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'star-btn p-0.5';
                    b.innerHTML =
                        `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>`;
                    b.addEventListener('click', () => {
                        val = i;
                        container.dataset.value = val;
                        Array.from(container.children).forEach((child, idx) => {
                            if (idx < val) child.classList.add('on');
                            else child.classList.remove('on');
                        });
                    });
                    container.appendChild(b);
                }
            });

            if (reviewText) {
                reviewText.addEventListener('input', () => {
                    charCount.textContent = reviewText.value.length;
                });
            }

            if (recommendSeg) {
                recommendSeg.querySelectorAll('button').forEach(btn => {
                    btn.addEventListener('click', () => {
                        recommendSeg.querySelectorAll('button').forEach(b => b.setAttribute(
                            'aria-pressed', 'false'));
                        btn.setAttribute('aria-pressed', 'true');
                        recommendVal = btn.dataset.val;
                    });
                });
            }

            if (anonSwitch) {
                anonSwitch.addEventListener('click', () => {
                    isAnon = !isAnon;
                    anonSwitch.setAttribute('aria-checked', isAnon ? 'true' : 'false');
                });
            }

            if (submitBtn) {
                submitBtn.addEventListener('click', async () => {
                    if (currentRating < 1) {
                        reviewError.textContent = 'Pilih rating bintang terlebih dahulu.';
                        reviewError.classList.remove('hidden');
                        return;
                    }
                    reviewError.classList.add('hidden');
                    submitBtn.setAttribute('disabled', 'true');
                    document.getElementById('submitText').textContent = 'Mengirim...';

                    const aspectsData = {};
                    document.querySelectorAll('[data-aspect]').forEach(el => {
                        if (el.dataset.value) aspectsData[el.dataset.aspect] = parseInt(el
                            .dataset.value);
                    });

                    const itemsData = {};
                    document.querySelectorAll('[data-item]').forEach(el => {
                        if (el.dataset.value) itemsData[el.dataset.item] = parseInt(el.dataset
                            .value);
                    });

                    const payload = {
                        rating: currentRating,
                        aspects: aspectsData,
                        items: itemsData,
                        tags: selectedTags,
                        comment: reviewText ? reviewText.value : '',
                        recommend: recommendVal,
                        anonymous: isAnon
                    };

                    try {
                        const response = await fetch(REVIEW_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: JSON.stringify(payload)
                        });

                        const resJson = await response.json();
                        if (response.ok && resJson.status === 'ok') {
                            reviewForm.classList.add('hidden');
                            reviewThanks.classList.remove('hidden');
                            document.getElementById('thanksMsg').textContent = resJson.message ||
                                'Ulasan berhasil disimpan.';
                            document.getElementById('thanksStars').innerHTML = '⭐'.repeat(
                                currentRating);

                            // Rating tinggi: tawarkan ulasan di Google Maps (jika URL diatur)
                            const g = document.getElementById('googleBtn');
                            if (g && currentRating >= 4) {
                                g.classList.remove('hidden');
                                g.classList.add('inline-flex');
                            }
                        } else {
                            throw new Error(resJson.message || 'Terjadi kesalahan.');
                        }
                    } catch (err) {
                        reviewError.textContent = err.message;
                        reviewError.classList.remove('hidden');
                        submitBtn.removeAttribute('disabled');
                        document.getElementById('submitText').textContent = 'Kirim ulasan';
                    }
                });
            }
        });
    </script>
</body>

</html>
