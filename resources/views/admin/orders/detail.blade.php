@extends('layout.template')

@section('content')
    @php
        $statuses = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        {{-- Tombol Navigasi & Aksi --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-amber-900 hover:underline">
                &larr; Kembali ke Daftar Pesanan
            </a>
            <div class="flex items-center gap-3">
                <button type="button" onclick="cetakStruk()"
                    class="rounded-lg bg-amber-800 px-4 py-2 font-semibold text-white hover:bg-amber-700">
                    🖨️ Cetak Struk
                </button>
                <form action="{{ route('admin.orders.destroy', $order) }}" method="post"
                    onsubmit="return confirm('Hapus pesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="rounded-lg border border-red-200 px-4 py-2 font-semibold text-red-700 hover:bg-red-50">
                        Hapus Pesanan
                    </button>
                </form>
            </div>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Detail Pesanan</p>
                <h1 class="mt-1 text-3xl font-bold text-stone-900">Pesanan #{{ $order->id }}</h1>
                <p class="mt-2 text-sm text-stone-600">{{ $order->created_at?->format('d M Y, H:i') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Info Card (Pelanggan, Meja, Metode Pembayaran) --}}
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Nama Pemesan</h2>
                <p class="mt-2 font-semibold text-stone-900">{{ $order->customer_name }}</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">No. Meja</h2>
                <p class="mt-2 font-semibold text-stone-900">{{ $order->table_number ?? '-' }}</p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">Metode Pembayaran</h2>
                <p class="mt-2 font-semibold text-stone-900">{{ ucfirst($order->payment_method ?? 'Tunai') }}</p>
            </div>
        </section>

        {{-- Ubah Status Pesanan --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-500">Ubah Status Pesanan</h2>
            <form action="{{ route('admin.orders.status', $order) }}" method="post"
                class="mt-3 flex flex-wrap items-center gap-3">
                @csrf
                @method('PATCH')
                <select id="order-status" name="status"
                    class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                    Simpan Status
                </button>
            </form>
        </section>

        {{-- Tabel Menu Dipesan --}}
        <section class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="p-5 border-b border-stone-100 flex justify-between items-center">
                <h2 class="font-bold text-stone-800">Daftar Menu Dipesan</h2>
                <span
                    class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-semibold">{{ $order->items->count() }}
                    item</span>
            </div>
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-600">
                    <tr>
                        <th class="px-5 py-4">Nama Menu</th>
                        <th class="px-5 py-4">Harga Satuan</th>
                        <th class="px-5 py-4">Jumlah</th>
                        <th class="px-5 py-4 text-right">Subtotal</th>
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
                        <tr>
                            <td class="px-5 py-4 font-medium text-stone-900">
                                {{ $item->menu?->name ?? 'Menu tidak tersedia' }}</td>
                            <td class="px-5 py-4 text-stone-700">Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-stone-700">x{{ $item->quantity }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-stone-900">
                                Rp {{ number_format($lineTotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-stone-500">
                                Rincian item tidak tersedia untuk pesanan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t border-stone-200 bg-stone-50 text-stone-700">
                    <tr>
                        <th colspan="3" class="px-5 py-3 text-right text-xs font-semibold uppercase">Subtotal</th>
                        <td class="px-5 py-3 text-right font-medium">Rp
                            {{ number_format($order->subtotal ?? $calculatedSubtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th colspan="3" class="px-5 py-2 text-right text-xs font-semibold uppercase">Pajak (11%)</th>
                        <td class="px-5 py-2 text-right font-medium">Rp
                            {{ number_format($order->tax ?? $calculatedSubtotal * 0.11, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t border-stone-200 text-stone-900">
                        <th colspan="3" class="px-5 py-4 text-right text-sm font-bold">Total Pembayaran</th>
                        <td class="px-5 py-4 text-right text-base font-bold text-emerald-700">
                            Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
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
