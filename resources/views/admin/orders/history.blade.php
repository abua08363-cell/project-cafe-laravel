@extends('layout.template')

@section('content')
    @php
        // Ubah nilai JSON / array / collection dari database menjadi array biasa
        $toArray = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }
            if ($value instanceof \Illuminate\Support\Collection) {
                return $value->all();
            }
            if (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : [];
            }

            return [];
        };

        // Label untuk penilaian per aspek (kunci sesuai yang disimpan saat ulasan dikirim)
        $aspectLabels = [
            'rasa' => 'Rasa',
            'pelayanan' => 'Pelayanan',
            'kecepatan' => 'Kecepatan',
            'suasana' => 'Suasana',
        ];

        $statusLabels = [
            'completed' => ['label' => 'SELESAI', 'class' => 'bg-emerald-100 text-emerald-800'],
            'processing' => ['label' => 'DIPROSES', 'class' => 'bg-blue-100 text-blue-800'],
            'cancelled' => ['label' => 'DIBATALKAN', 'class' => 'bg-red-100 text-red-800'],
            'pending' => ['label' => 'PENDING', 'class' => 'bg-amber-100 text-amber-800'],
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Judul & Sambutan --}}
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">
                BrewNest Coffee
            </p>
            <h1 class="mt-1 text-3xl font-bold text-stone-900">
                Riwayat Pesanan
            </h1>
            <p class="mt-2 text-sm text-stone-600">
                Arsip seluruh transaksi dan pesanan yang telah lunas / selesai disajikan.
            </p>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            {{-- Total Transaksi --}}
            <div class="flex items-center rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="mr-4 rounded-xl bg-amber-50 p-3 text-amber-600">📋</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-stone-800">{{ $totalTransaksi ?? 0 }}</h3>
                </div>
            </div>

            {{-- Total Pendapatan --}}
            <div class="flex items-center rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="mr-4 rounded-xl bg-emerald-50 p-3 text-emerald-600">💰</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Total Pendapatan</p>
                    <h3 class="text-2xl font-bold text-stone-800">Rp
                        {{ number_format((float) ($totalPendapatan ?? 0), 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Rata-rata Rating --}}
            <div class="flex items-center rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="mr-4 rounded-xl bg-yellow-50 p-3 text-yellow-600">⭐</div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Rata-rata Rating
                        ({{ $totalReviewCount ?? 0 }} ulasan)</p>
                    <h3 class="text-2xl font-bold text-stone-800">{{ number_format((float) ($avgRating ?? 0), 1) }} / 5
                    </h3>
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="rounded-xl bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabel Riwayat Pesanan --}}
        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
            <table class="w-full min-w-[1100px] text-left text-sm">

                <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-600">
                    <tr>
                        <th class="px-5 py-4">No. Meja</th>
                        <th class="px-5 py-4">Nama Pemesan</th>
                        <th class="px-5 py-4">Item Menu</th>
                        <th class="px-5 py-4">Total Bayar</th>
                        <th class="px-5 py-4">Ulasan &amp; Rating</th>
                        <th class="px-5 py-4">Status Pembayaran</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse ($orders as $order)
                        @php
                            $review = $order->review ?? null;
                            $rating = (int) ($review->rating ?? 0);
                            $comment = $review->comment ?? null;

                            $tags = $review ? $toArray($review->tags ?? null) : [];
                            $aspects = $review ? $toArray($review->aspects ?? null) : [];
                            $itemRatings = $review ? $toArray($review->item_ratings ?? null) : [];

                            $status = strtolower((string) ($order->status ?? 'pending'));
                            $currentStatus = $statusLabels[$status] ?? [
                                'label' => strtoupper($status),
                                'class' => 'bg-stone-100 text-stone-800',
                            ];
                        @endphp

                        <tr class="align-top hover:bg-stone-50">

                            {{-- Nomor meja --}}
                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-900">
                                    {{ $order->table_number ?? ($order->table_no ?? '-') }}
                                </span>
                            </td>

                            {{-- Nama pemesan --}}
                            <td class="px-5 py-5">
                                <p class="font-semibold text-stone-800">{{ $order->customer_name }}</p>
                            </td>

                            {{-- Item menu (+ bintang per menu kalau ada) --}}
                            <td class="px-5 py-5">
                                @forelse ($order->items as $item)
                                    @php
                                        $menuStar = $itemRatings[$item->menu_id] ?? null;
                                    @endphp
                                    <p class="mb-1 text-stone-700">
                                        {{ $item->menu->name ?? ($item->menu->title ?? 'Menu') }}
                                        <span class="text-stone-400">(x{{ $item->quantity }})</span>
                                        @if (is_numeric($menuStar) && (int) $menuStar > 0)
                                            <span class="ml-1 whitespace-nowrap text-xs font-semibold text-amber-600"
                                                title="Rating menu ini">★ {{ (int) $menuStar }}/5</span>
                                        @endif
                                    </p>
                                @empty
                                    <span class="text-stone-400">Tidak ada item</span>
                                @endforelse
                            </td>

                            {{-- Total bayar --}}
                            <td class="whitespace-nowrap px-5 py-5 font-bold text-emerald-700">
                                Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}
                            </td>

                            {{-- Ulasan dan rating --}}
                            <td class="px-5 py-5">
                                @if ($review)
                                    <div class="flex items-center space-x-2 whitespace-nowrap">
                                        <span class="text-amber-500">
                                            @for ($i = 1; $i <= 5; $i++)
                                                {{ $i <= $rating ? '★' : '☆' }}
                                            @endfor
                                        </span>
                                        <span class="font-semibold text-amber-700">{{ $rating }}/5</span>

                                        @if ($review->anonymous)
                                            <span
                                                class="rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-500">Anonim</span>
                                        @endif
                                    </div>

                                    {{-- Penilaian per aspek --}}
                                    @php
                                        $shownAspects = [];
                                        foreach ($aspectLabels as $key => $label) {
                                            $val = $aspects[$key] ?? null;
                                            if (is_numeric($val) && (int) $val > 0) {
                                                $shownAspects[$label] = (int) $val;
                                            }
                                        }
                                    @endphp
                                    @if ($shownAspects)
                                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-stone-600">
                                            @foreach ($shownAspects as $label => $val)
                                                <span class="whitespace-nowrap">{{ $label }}
                                                    <span class="font-semibold text-amber-600">★ {{ $val }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Komentar --}}
                                    @if ($comment)
                                        <p class="mt-2 max-w-xs italic text-stone-600">"{{ $comment }}"</p>
                                    @endif

                                    {{-- Tags --}}
                                    @if (!empty($tags))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach ($tags as $tag)
                                                <span
                                                    class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <span class="italic text-stone-400">Belum diulas</span>
                                @endif
                            </td>

                            {{-- Status pesanan --}}
                            <td class="px-5 py-5">
                                <span
                                    class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $currentStatus['class'] }}">
                                    {{ $currentStatus['label'] }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-5">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="inline-block whitespace-nowrap rounded-lg bg-stone-800 px-4 py-2 font-semibold text-white hover:bg-stone-700">
                                    Lihat Nota →
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-stone-500">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>
@endsection
