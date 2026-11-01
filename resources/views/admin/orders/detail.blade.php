@extends('layout.template')

@section('content')
    @php
        $statuses = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $statusStyles = [
            'completed' => ['class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'dot' => 'bg-emerald-500'],
            'processing' => ['class' => 'bg-blue-50 text-blue-700 ring-blue-200', 'dot' => 'bg-blue-500'],
            'cancelled' => ['class' => 'bg-rose-50 text-rose-700 ring-rose-200', 'dot' => 'bg-rose-500'],
            'pending' => ['class' => 'bg-amber-50 text-amber-800 ring-amber-200', 'dot' => 'bg-amber-500'],
        ];
        $currentStyle = $statusStyles[$order->status] ?? [
            'class' => 'bg-stone-100 text-stone-700 ring-stone-200',
            'dot' => 'bg-stone-400',
        ];
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-8">

        {{-- Tombol Navigasi & Aksi --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.orders.index') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#754a30] transition hover:text-[#21140e]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Kembali ke Daftar Pesanan
            </a>
            <div class="flex items-center gap-3">
                <button type="button" onclick="cetakStruk()"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#21140e] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#754a30] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#754a30]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
                    </svg>
                    Cetak Struk
                </button>
                <form action="{{ route('admin.orders.destroy', $order) }}" method="post"
                    onsubmit="return confirm('Hapus pesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-600 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0l1 14h8l1-14" />
                        </svg>
                        Hapus Pesanan
                    </button>
                </form>
            </div>
        </div>

        {{-- Judul --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b9854f]">Detail Pesanan</p>
                <h1 class="mt-1.5 font-serif text-3xl font-bold tracking-tight text-[#21140e] md:text-4xl">Pesanan
                    #{{ $order->id }}</h1>
                <p class="mt-2 text-sm text-stone-500">{{ $order->created_at?->format('d M Y, H:i') }}</p>
            </div>
            <span
                class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold ring-1 ring-inset {{ $currentStyle['class'] }}">
                <span class="h-2 w-2 rounded-full {{ $currentStyle['dot'] }}"></span>
                {{ $statuses[$order->status] ?? ucfirst((string) $order->status) }}
            </span>
        </div>

        @if (session('success'))
            <div role="status"
                class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.3 3.9L2.4 17.5A2 2 0 004.1 20.5h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Info Card (Pelanggan, Meja, Metode Pembayaran) --}}
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#754a30]/10 text-[#754a30]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xs font-medium text-stone-500">Nama Pemesan</h2>
                    <p class="mt-0.5 truncate font-semibold text-[#21140e]">{{ $order->customer_name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h16M6 9v10M18 9v10M3 9l1.5-5h15L21 9" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xs font-medium text-stone-500">No. Meja</h2>
                    <p class="mt-0.5 truncate font-semibold text-[#21140e]">{{ $order->table_number ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7h18v10H3zM12 14a2 2 0 100-4 2 2 0 000 4z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xs font-medium text-stone-500">Metode Pembayaran</h2>
                    <p class="mt-0.5 truncate font-semibold text-[#21140e]">
                        {{ ucfirst($order->payment_method ?? 'Tunai') }}</p>
                </div>
            </div>
        </section>

        {{-- Ubah Status Pesanan --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-[#21140e]">Ubah Status Pesanan</h2>
                    <p class="mt-0.5 text-sm text-stone-500">Perbarui progres pesanan agar sesuai kondisi terkini.</p>
                </div>
                <form action="{{ route('admin.orders.status', $order) }}" method="post"
                    class="flex flex-wrap items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <label for="order-status" class="sr-only">Status pesanan</label>
                    <select id="order-status" name="status"
                        class="rounded-xl border border-stone-300 bg-white py-2.5 pl-3.5 pr-9 text-sm text-stone-800 focus:border-[#754a30] focus:outline-none focus:ring-2 focus:ring-[#754a30]/20">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="rounded-xl bg-[#754a30] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#21140e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#754a30]">
                        Simpan Status
                    </button>
                </form>
            </div>
        </section>

        {{-- Tabel Menu Dipesan --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4 md:px-6">
                <h2 class="font-semibold text-[#21140e]">Daftar Menu Dipesan</h2>
                <span
                    class="rounded-full bg-[#754a30]/10 px-3 py-1 text-xs font-semibold text-[#754a30]">{{ $order->items->count() }}
                    item</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-stone-200 bg-stone-50 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                            <th class="px-5 py-3.5 md:px-6">Nama Menu</th>
                            <th class="px-5 py-3.5">Harga Satuan</th>
                            <th class="px-5 py-3.5">Jumlah</th>
                            <th class="px-5 py-3.5 text-right md:px-6">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @php
                            $calculatedSubtotal = 0;
                        @endphp
                        @forelse ($order->items as $item)
                            @php
                                $lineTotal = (float) $item->price * $item->quantity;
                                $calculatedSubtotal += $lineTotal;
                            @endphp
                            <tr class="transition-colors hover:bg-[#fbf8f5]">
                                <td class="px-5 py-4 font-medium text-[#21140e] md:px-6">
                                    {{ $item->menu?->name ?? 'Menu tidak tersedia' }}</td>
                                <td class="px-5 py-4 tabular-nums text-stone-600">Rp
                                    {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 tabular-nums text-stone-600">x{{ $item->quantity }}</td>
                                <td class="px-5 py-4 text-right font-semibold tabular-nums text-[#21140e] md:px-6">
                                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-stone-500">
                                    Rincian item tidak tersedia untuk pesanan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-stone-200 bg-stone-50 text-stone-700">
                        <tr>
                            <th colspan="3"
                                class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">
                                Subtotal</th>
                            <td class="px-5 py-3 text-right font-medium tabular-nums md:px-6">Rp
                                {{ number_format($order->subtotal ?? $calculatedSubtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th colspan="3"
                                class="px-5 py-2 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">
                                Pajak (11%)</th>
                            <td class="px-5 py-2 text-right font-medium tabular-nums md:px-6">Rp
                                {{ number_format($order->tax ?? $calculatedSubtotal * 0.11, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-t border-stone-200 bg-white text-[#21140e]">
                            <th colspan="3" class="px-5 py-4 text-right text-sm font-bold">Total Pembayaran</th>
                            <td class="px-5 py-4 text-right text-lg font-bold tabular-nums text-[#754a30] md:px-6">
                                Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>

    {{-- ====================== TEMPLATE STRUK (tidak tampil di layar) ====================== --}}
    @php
        $cfg = \App\Models\Setting::query()->pluck('value', 'key')->all();
        $storeName = $cfg['store_name'] ?? 'BrewNest';
        $storeAddress = $cfg['store_address'] ?? '';
        $storePhone = $cfg['phone_number'] ?? '';

        $orderCode = 'BRW' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
        $subtotalValue = (float) ($order->subtotal ?? $calculatedSubtotal);
        $taxValue = (float) ($order->tax ?? $calculatedSubtotal * 0.11);
        $totalValue = (float) $order->total_price;
        $isPaid = $order->status === 'completed';
        $statusLabel = $isPaid ? 'LUNAS' : strtoupper($statuses[$order->status] ?? $order->status);

        // Barcode dekoratif (konsisten per kode pesanan)
        $barWidths = array_map(fn($c) => (hexdec($c) % 3) + 1, str_split(md5($orderCode)));
    @endphp

    <div id="struk-source" style="display:none">
        <div class="receipt">
            <div class="center">
                <div class="logo">☕</div>
                <div class="brand">{{ $storeName }}</div>
                <div class="sub">COFFEE &amp; EATERY</div>
                @if ($storeAddress)
                    <div class="small">{{ $storeAddress }}</div>
                @endif
                @if ($storePhone)
                    <div class="small">Telp. {{ $storePhone }}</div>
                @endif
            </div>

            <div class="dash"></div>

            @if ($isPaid)
                <div class="stamp">LUNAS</div>
            @endif

            <div class="row"><span>No. Pesanan</span><span>#{{ $orderCode }}</span></div>
            <div class="row"><span>Tanggal</span><span>{{ $order->created_at?->format('d/m/Y') }}</span></div>
            <div class="row"><span>Jam</span><span>{{ $order->created_at?->format('H:i') }} WIB</span></div>
            <div class="row"><span>Pemesan</span><span>{{ $order->customer_name }}</span></div>
            <div class="row"><span>Meja/Alamat</span><span>{{ $order->table_number ?? '-' }}</span></div>

            <div class="dash"></div>

            @foreach ($order->items as $item)
                <div class="item">
                    <div class="item-name">{{ $item->menu?->name ?? 'Menu tidak tersedia' }}</div>
                    <div class="row">
                        <span>{{ $item->quantity }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format((float) $item->price * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach

            <div class="dash"></div>

            <div class="row"><span>Subtotal ({{ $order->items->count() }} item)</span><span>Rp
                    {{ number_format($subtotalValue, 0, ',', '.') }}</span></div>
            <div class="row"><span>Pajak</span><span>Rp {{ number_format($taxValue, 0, ',', '.') }}</span></div>

            <div class="solid"></div>

            <div class="row total"><span>TOTAL</span><span>Rp {{ number_format($totalValue, 0, ',', '.') }}</span></div>

            <div class="row small-row"><span>Metode bayar</span><span
                    class="bold">{{ strtoupper($order->payment_method ?? 'Tunai') }}</span></div>
            <div class="row small-row"><span>Status</span><span class="bold">{{ $statusLabel }}</span></div>

            <div class="dash"></div>

            <div class="bars">
                @foreach ($barWidths as $i => $w)
                    <span style="width:{{ $w }}px;background:{{ $i % 2 === 0 ? '#000' : '#fff' }}"></span>
                @endforeach
            </div>
            <div class="center code">#{{ $orderCode }}</div>

            <div class="dash"></div>

            <div class="center">
                <div class="bold">TERIMA KASIH</div>
                <div class="small">Simpan struk ini sebagai bukti pembayaran.</div>
                <div class="small">Sampai jumpa lagi di {{ $storeName }} ☕</div>
            </div>
        </div>
    </div>

    {{-- CSS struk (tidak diterapkan di layar, hanya dibaca oleh script) --}}
    <style id="struk-css" media="not all">
        @@page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 8mm 5mm;
            position: relative;
        }

        .center {
            text-align: center;
        }

        .logo {
            font-size: 26px;
            line-height: 1;
        }

        .brand {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 24px;
            font-weight: 700;
            margin-top: 4px;
        }

        .sub {
            font-size: 8px;
            letter-spacing: 3px;
            margin: 2px 0 6px;
        }

        .small {
            font-size: 9px;
            line-height: 1.5;
        }

        .bold {
            font-weight: 700;
        }

        .dash {
            border-top: 1px dashed #555;
            margin: 10px 0;
        }

        .solid {
            border-top: 2px solid #444;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            line-height: 1.55;
        }

        .row span:last-child {
            text-align: right;
        }

        .item {
            margin-bottom: 6px;
        }

        .item-name {
            font-weight: 700;
        }

        .total {
            font-size: 15px;
            font-weight: 700;
            align-items: baseline;
        }

        .small-row {
            font-size: 9px;
        }

        .bars {
            display: flex;
            justify-content: center;
            height: 42px;
            margin-top: 4px;
        }

        .bars span {
            display: block;
            height: 100%;
        }

        .code {
            font-size: 8px;
            letter-spacing: 6px;
            margin-top: 4px;
        }

        .stamp {
            position: absolute;
            right: 18px;
            margin-top: -6px;
            border: 2px solid #1f9d6b;
            color: #1f9d6b;
            font-weight: 700;
            letter-spacing: 3px;
            font-size: 13px;
            padding: 2px 10px;
            border-radius: 6px;
            transform: rotate(-8deg);
        }
    </style>

    <script>
        function cetakStruk() {
            var source = document.getElementById('struk-source');
            var css = document.getElementById('struk-css').textContent;

            var iframe = document.createElement('iframe');
            iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
            document.body.appendChild(iframe);

            var doc = iframe.contentWindow.document;
            doc.open();
            doc.close();

            var style = doc.createElement('style');
            style.textContent = css;
            doc.head.appendChild(style);

            doc.title = 'Struk {{ $orderCode }}';
            doc.body.innerHTML = source.innerHTML;

            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                setTimeout(function() {
                    iframe.remove();
                }, 1500);
            }, 300);
        }
    </script>
@endsection
