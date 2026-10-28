<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Metode Pembayaran - BrewNest Coffee' }}</title>
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
        /* ---------- Urutan muncul saat halaman dibuka ---------- */
        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rise {
            opacity: 0;
            animation: rise .65s cubic-bezier(.2, .7, .2, 1) forwards;
        }

        .d1 {
            animation-delay: .05s;
        }

        .d2 {
            animation-delay: .15s;
        }

        .d3 {
            animation-delay: .25s;
        }

        .d4 {
            animation-delay: .35s;
        }

        /* ---------- Panel detail membuka halus ---------- */
        .collapse-panel {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows .45s cubic-bezier(.2, .7, .2, 1), opacity .35s ease;
        }

        .collapse-panel.open {
            grid-template-rows: 1fr;
            opacity: 1;
        }

        .collapse-panel>div {
            overflow: hidden;
            min-height: 0;
        }

        /* ---------- Garis scan di atas QR ---------- */
        @keyframes scan {
            0% {
                top: 6%;
                opacity: 0;
            }

            15% {
                opacity: 1;
            }

            85% {
                opacity: 1;
            }

            100% {
                top: 94%;
                opacity: 0;
            }
        }

        .scan-line {
            position: absolute;
            left: 8px;
            right: 8px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #d7a66c, transparent);
            box-shadow: 0 0 14px 3px rgba(215, 166, 108, .6);
            animation: scan 2.6s ease-in-out infinite;
        }

        /* ---------- Tanda centang pada metode terpilih ---------- */
        .radio-dot {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            border-radius: 9999px;
            border: 2px solid #d6d3d1;
            display: grid;
            place-items: center;
            transition: border-color .2s, background-color .2s;
        }

        .radio-dot::after {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #fff;
            transform: scale(0);
            transition: transform .25s cubic-bezier(.3, 1.6, .5, 1);
        }

        input:checked+.radio-dot {
            border-color: #754a30;
            background: #754a30;
        }

        input:checked+.radio-dot::after {
            transform: scale(1);
        }

        /* ---------- Item ringkasan masuk bergantian ---------- */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .item-in {
            opacity: 0;
            animation: slideIn .45s ease forwards;
        }

        /* ---------- Total berdenyut saat berubah ---------- */
        @keyframes pop {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.06);
            }
        }

        .pop {
            animation: pop .4s ease;
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* ---------- Toast ---------- */
        .toast {
            transform: translate(-50%, 24px);
            opacity: 0;
            pointer-events: none;
            transition: transform .4s cubic-bezier(.2, .7, .2, 1), opacity .3s ease;
        }

        .toast.show {
            transform: translate(-50%, 0);
            opacity: 1;
        }

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

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }

            .rise,
            .item-in {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-cream/60 font-sans text-coffee antialiased">

    <!-- Header -->
    <header class="sticky top-0 z-20 bg-coffee py-4 px-6 text-white shadow-lg shadow-coffee/20">
        <div class="mx-auto flex max-w-5xl items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-display text-xl font-bold">
                <span>☕</span> BrewNest Coffee &amp; Eatery
            </a>
            <a href="{{ url('/') }}"
                class="group inline-flex items-center gap-1.5 rounded text-xs text-amber-200 transition hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel">
                <span class="transition-transform group-hover:-translate-x-1">←</span> Lanjut belanja
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10">

        <!-- Langkah checkout (urutan nyata: keranjang → bayar → selesai) -->
        <ol class="rise d1 mb-8 flex items-center gap-3 text-xs font-semibold" aria-label="Langkah pemesanan">
            <li class="flex items-center gap-2 text-brown">
                <span class="grid h-6 w-6 place-items-center rounded-full bg-brown text-white">✓</span> Keranjang
            </li>
            <span class="h-px w-8 bg-brown/40"></span>
            <li class="flex items-center gap-2" aria-current="step">
                <span
                    class="grid h-6 w-6 place-items-center rounded-full bg-coffee text-white ring-4 ring-caramel/40">2</span>
                Pembayaran
            </li>
            <span class="h-px w-8 bg-stone-300"></span>
            <li class="flex items-center gap-2 text-stone-400">
                <span class="grid h-6 w-6 place-items-center rounded-full border border-stone-300">3</span> Selesai
            </li>
        </ol>

        <h1 class="rise d1 mb-8 font-display text-3xl font-bold">Informasi pembayaran</h1>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">

            <!-- Pilih Metode Pembayaran -->
            <div class="space-y-5 md:col-span-2">
                <div class="rise d2 space-y-3 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-stone-700">Pilih metode pembayaran</h2>

                    <!-- Tunai -->
                    <label
                        class="flex cursor-pointer items-center justify-between rounded-xl border border-stone-200 p-4 transition hover:-translate-y-0.5 hover:border-caramel hover:shadow-md has-[:checked]:border-brown has-[:checked]:bg-amber-50/60 has-[:checked]:shadow-md has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-caramel">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">💵</span>
                            <div>
                                <p class="text-sm font-bold">Tunai (Cash)</p>
                                <p class="text-xs text-stone-500">Bayar langsung saat transaksi di kasir/meja.</p>
                            </div>
                        </div>
                        <input type="radio" name="payment_method" value="Tunai" checked class="sr-only"
                            onclick="togglePaymentDetail('tunai')">
                        <span class="radio-dot"></span>
                    </label>

                    <!-- QRIS -->
                    <label
                        class="flex cursor-pointer items-center justify-between rounded-xl border border-stone-200 p-4 transition hover:-translate-y-0.5 hover:border-caramel hover:shadow-md has-[:checked]:border-brown has-[:checked]:bg-amber-50/60 has-[:checked]:shadow-md has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-caramel">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📱</span>
                            <div>
                                <p class="text-sm font-bold">QRIS</p>
                                <p class="text-xs text-stone-500">Scan QR untuk m-banking / e-wallet (GoPay, OVO, Dana,
                                    dll).</p>
                            </div>
                        </div>
                        <input type="radio" name="payment_method" value="QRIS" class="sr-only"
                            onclick="togglePaymentDetail('qris')">
                        <span class="radio-dot"></span>
                    </label>

                    <!-- Area Detail QRIS -->
                    <div id="qrisDetail" class="collapse-panel">
                        <div>
                            <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50 p-4 text-center">
                                <p class="text-xs font-bold text-stone-700">Scan QR code di bawah dengan aplikasi
                                    pembayaranmu:</p>
                                <div class="relative inline-block rounded-xl border bg-white p-2 shadow-sm">
                                    <img src="{{ asset('uploads/menus/qr.jpeg') }}" alt="QRIS Code"
                                        class="mx-auto h-48 w-48 object-contain"
                                        onerror="this.onerror=null; this.classList.add('hidden'); document.getElementById('qrisFallback').classList.remove('hidden'); document.getElementById('scanLine').classList.add('hidden');">
                                    <div id="qrisFallback"
                                        class="hidden flex h-48 w-48 items-center justify-center text-xs italic text-stone-400">
                                        QR belum tersedia.<br>Silakan minta kasir.
                                    </div>
                                    <span id="scanLine" class="scan-line" aria-hidden="true"></span>
                                </div>
                                <p class="text-[11px] text-stone-500">Didukung: BCA, Mandiri, GoPay, OVO, DANA,
                                    ShopeePay</p>
                            </div>
                        </div>
                    </div>

                    <!-- Debit / Kredit -->
                    <label
                        class="flex cursor-pointer items-center justify-between rounded-xl border border-stone-200 p-4 transition hover:-translate-y-0.5 hover:border-caramel hover:shadow-md has-[:checked]:border-brown has-[:checked]:bg-amber-50/60 has-[:checked]:shadow-md has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-caramel">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">💳</span>
                            <div>
                                <p class="text-sm font-bold">Debit / Kredit</p>
                                <p class="text-xs text-stone-500">Gesek atau tap kartu di mesin EDC.</p>
                            </div>
                        </div>
                        <input type="radio" name="payment_method" value="Debit / Kredit" class="sr-only"
                            onclick="togglePaymentDetail('card')">
                        <span class="radio-dot"></span>
                    </label>

                    <!-- Area Pilihan Bank -->
                    <div id="cardDetail" class="collapse-panel">
                        <div>
                            <div class="space-y-3 rounded-xl border border-stone-200 bg-stone-50 p-4">
                                <p class="text-xs font-bold text-stone-700">Pilih jenis kartu / bank:</p>
                                <div id="bankOptions" class="grid grid-cols-2 gap-2 text-xs sm:grid-cols-3"></div>

                                <div id="bankInfo"
                                    class="space-y-2 rounded-lg border border-stone-200 bg-white p-3 text-xs">
                                    <p id="bankInfoText" class="text-stone-600"></p>
                                    <a id="bankInfoLink" href="#" target="_blank" rel="noopener noreferrer"
                                        class="inline-block font-bold text-brown hover:underline"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rise d3 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <label for="orderNotes" class="mb-2 block text-sm font-bold text-stone-700">Catatan pesanan
                        (opsional)</label>
                    <textarea id="orderNotes" rows="2" maxlength="500" placeholder="Contoh: tanpa gula, extra shot, es sedikit..."
                        class="w-full rounded-xl border border-stone-200 p-3 text-sm outline-none transition placeholder:text-stone-400 focus:border-brown focus:ring-2 focus:ring-caramel/50"></textarea>
                </div>
            </div>

            <!-- Ringkasan -->
            <aside
                class="rise d4 h-fit space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm md:sticky md:top-24">
                <h3 class="border-b border-stone-100 pb-3 font-display text-lg font-bold">Ringkasan pesanan</h3>

                <div id="checkoutSummaryList" class="max-h-52 space-y-2.5 overflow-y-auto pr-1 text-sm"></div>

                <div class="space-y-1.5 border-t border-dashed border-stone-300 pt-3 text-sm">
                    <div class="flex justify-between text-stone-500">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-stone-500">
                        <span>Pajak (11%)</span>
                        <span id="summaryTax">Rp 0</span>
                    </div>
                    <div
                        class="flex items-baseline justify-between border-t border-stone-200 pt-3 font-extrabold text-brown">
                        <span class="text-sm">Total pembayaran</span>
                        <span id="summaryTotal" class="origin-right text-xl">Rp 0</span>
                    </div>
                </div>

                <button id="payButton" onclick="submitOrder()"
                    class="btn-brew flex w-full items-center justify-center gap-2 rounded-xl bg-brown py-3.5 text-sm font-bold text-white shadow-lg shadow-brown/25 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80">
                    <span id="payLabel">Bayar sekarang</span>
                </button>
            </aside>
        </div>
    </main>

    <!-- Toast -->
    <div id="toast" role="status" aria-live="polite"
        class="toast fixed bottom-6 left-1/2 z-50 max-w-[90%] rounded-xl bg-coffee px-5 py-3 text-sm text-white shadow-2xl">
    </div>

    <script>
        const cartData = JSON.parse(localStorage.getItem('brewNestCart')) || [];
        const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
        const escapeHtml = s => String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));

        // Hitung naik dari angka lama ke angka baru
        function countUp(el, to, duration = 800) {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce) {
                el.textContent = rupiah(to);
                return;
            }
            const start = performance.now();

            function frame(now) {
                const t = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - t, 3);
                el.textContent = rupiah(to * eased);
                if (t < 1) requestAnimationFrame(frame);
                else {
                    el.classList.remove('pop');
                    void el.offsetWidth;
                    el.classList.add('pop');
                }
            }
            requestAnimationFrame(frame);
        }

        function showToast(message) {
            const t = document.getElementById('toast');
            t.textContent = message;
            t.classList.add('show');
            clearTimeout(showToast.timer);
            showToast.timer = setTimeout(() => t.classList.remove('show'), 3200);
        }

        function renderCheckoutSummary() {
            const container = document.getElementById('checkoutSummaryList');
            let subtotal = 0;

            if (cartData.length === 0) {
                container.innerHTML = `
                    <div class="py-6 text-center">
                        <div class="mb-2 text-3xl">🫗</div>
                        <p class="text-stone-500">Keranjang masih kosong.</p>
                        <a href="{{ url('/') }}" class="mt-1 inline-block font-bold text-brown hover:underline">Pilih menu dulu</a>
                    </div>`;
                return;
            }

            let html = '';
            cartData.forEach((item, i) => {
                const totalItem = item.price * item.qty;
                subtotal += totalItem;
                html += `
                    <div class="item-in flex justify-between gap-3" style="animation-delay:${0.45 + i * 0.07}s">
                        <span>${escapeHtml(item.name)} <span class="text-stone-400">×${item.qty}</span></span>
                        <span class="font-bold whitespace-nowrap">${rupiah(totalItem)}</span>
                    </div>`;
            });

            const tax = subtotal * 0.11;
            const grandTotal = subtotal + tax;

            container.innerHTML = html;
            document.getElementById('summarySubtotal').textContent = rupiah(subtotal);
            document.getElementById('summaryTax').textContent = rupiah(tax);
            countUp(document.getElementById('summaryTotal'), grandTotal);
        }

        renderCheckoutSummary();

        // Data bank: tiap bank punya nama, info, dan link resmi masing-masing
        const banks = [{
                value: 'BCA Debit/Credit',
                label: 'BCA',
                url: 'https://www.bca.co.id',
                info: 'Tap atau gesek kartu BCA di mesin EDC BCA.'
            },
            {
                value: 'Mandiri Debit/Credit',
                label: 'Mandiri',
                url: 'https://bankmandiri.co.id',
                info: 'Tap atau gesek kartu Mandiri di mesin EDC Mandiri.'
            },
            {
                value: 'BRI EDC',
                label: 'BRI',
                url: 'https://bri.co.id',
                info: 'Tap atau gesek kartu BRI di mesin EDC BRI.'
            },
            {
                value: 'BNI EDC',
                label: 'BNI',
                url: 'https://www.bni.co.id',
                info: 'Tap atau gesek kartu BNI di mesin EDC BNI.'
            },
            {
                value: 'Visa',
                label: 'Visa',
                url: 'https://www.visa.co.id',
                info: 'Kartu berlogo Visa bisa digesek atau di-tap di mesin EDC.'
            },
            {
                value: 'Mastercard',
                label: 'Mastercard',
                url: 'https://www.mastercard.co.id',
                info: 'Kartu berlogo Mastercard bisa digesek atau di-tap di mesin EDC.'
            }
        ];

        function renderBankOptions() {
            const wrap = document.getElementById('bankOptions');
            wrap.innerHTML = banks.map((b, i) => `
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border bg-white p-3 transition hover:border-caramel has-[:checked]:border-brown has-[:checked]:bg-amber-50/60 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-caramel">
                    <input type="radio" name="bank_option" value="${b.value}" class="sr-only"
                           ${i === 0 ? 'checked' : ''} onchange="showBankInfo(${i})">
                    <span class="radio-dot"></span>
                    <span class="font-semibold">${b.label}</span>
                </label>
            `).join('');
            showBankInfo(0);
        }

        function showBankInfo(index) {
            const b = banks[index];
            const box = document.getElementById('bankInfo');
            document.getElementById('bankInfoText').textContent = b.info;
            const link = document.getElementById('bankInfoLink');
            link.href = b.url;
            link.textContent = 'Kunjungi situs ' + b.label + ' ↗';
            box.classList.remove('item-in');
            void box.offsetWidth;
            box.classList.add('item-in');
            box.style.animationDelay = '0s';
        }

        renderBankOptions();

        // Tampilkan detail pembayaran dinamis (QRIS / kartu) dengan animasi buka-tutup
        function togglePaymentDetail(type) {
            document.getElementById('qrisDetail').classList.toggle('open', type === 'qris');
            document.getElementById('cardDetail').classList.toggle('open', type === 'card');
        }

        function submitOrder() {
            const button = document.getElementById('payButton');
            const label = document.getElementById('payLabel');

            if (cartData.length === 0) {
                showToast('Keranjang belanja kosong!');
                button.classList.remove('shake');
                void button.offsetWidth;
                button.classList.add('shake');
                return;
            }

            let selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;

            // Jika metode pembayaran kartu, tambahkan detail bank yang dipilih
            if (selectedPayment === 'Debit / Kredit') {
                const selectedBank = document.querySelector('input[name="bank_option"]:checked');
                if (selectedBank) {
                    selectedPayment += ' (' + selectedBank.value + ')';
                }
            }

            const customerName = localStorage.getItem('customerName') || 'Tamu';
            const tableNumber = localStorage.getItem('customerTable') || 'Takeaway';
            const notes = document.getElementById('orderNotes').value.trim();

            // Tombol masuk mode loading
            button.disabled = true;
            label.textContent = 'Memproses pesanan...';
            button.insertAdjacentHTML('afterbegin', '<span class="spinner" aria-hidden="true"></span>');

            const resetButton = () => {
                button.disabled = false;
                label.textContent = 'Bayar sekarang';
                const sp = button.querySelector('.spinner');
                if (sp) sp.remove();
            };

            fetch('{{ url('checkout/process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        cart: cartData,
                        customer_name: customerName,
                        table_number: tableNumber,
                        payment_method: selectedPayment,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        localStorage.removeItem('brewNestCart');
                        label.textContent = 'Pesanan diterima';
                        showToast('Pesanan diterima. Mengalihkan...');
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 700);
                    } else {
                        resetButton();
                        showToast('Gagal memproses pesanan: ' + (data.message || 'terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resetButton();
                    showToast('Koneksi bermasalah. Coba lagi.');
                });
        }
    </script>
</body>

</html>
