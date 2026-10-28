@extends('layout.template')

@section('content')
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

        {{-- Statistik Card (Mirip CI4) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            {{-- Total Transaksi --}}
            <div class="flex items-center rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="mr-4 rounded-xl bg-amber-50 p-3 text-amber-600">
                    📋
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-stone-800">{{ $totalTransaksi ?? 0 }}</h3>
                </div>
            </div>

            {{-- Total Pendapatan --}}
            <div class="flex items-center rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="mr-4 rounded-xl bg-emerald-50 p-3 text-emerald-600">
                    💰
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">Total Pendapatan</p>
                    <h3 class="text-2xl font-bold text-stone-800">Rp
                        {{ number_format((float) ($totalPendapatan ?? 0), 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Rata-rata Rating --}}
            <div class="flex items-center rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="mr-4 rounded-xl bg-yellow-50 p-3 text-yellow-600">
                    ⭐
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-stone-500 font-semibold">Rata-rata Rating
                        ({{ $totalReviewCount ?? 0 }} ulasan)</p>
                    <h3 class="text-2xl font-bold text-stone-800">{{ number_format((float) ($avgRating ?? 0), 1) }} / 5</h3>
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
                        <th class="px-5 py-4">Ulasan & Rating</th>
                        <th class="px-5 py-4">Status Pembayaran</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse ($orders as $order)
                        <tr class="align-top hover:bg-stone-50">

                            {{-- Nomor meja --}}
                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-900">
                                    {{ $order->table_number ?? ($order->table_no ?? '-') }}
                                </span>
                            </td>

                            {{-- Nama pemesan --}}
                            <td class="px-5 py-5">
                                <p class="font-semibold text-stone-800">
                                    {{ $order->customer_name }}
                                </p>
                            </td>

                            {{-- Item menu --}}
                            <td class="px-5 py-5">
                                @forelse ($order->items as $item)
                                    <p class="mb-1 text-stone-700">
                                        {{ $item->menu->name ?? ($item->menu->title ?? 'Menu') }}
                                        <span class="text-stone-400">
                                            (x{{ $item->quantity }})
                                        </span>
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
                            {{-- Ulasan dan rating --}}
                            <td class="px-5 py-5">
                                @php
                                    $review = $order->review ?? null;
                                    $rating = (int) ($review->rating ?? 0);
                                    $comment = $review->comment ?? null;

                                    // Ambil data tags (jika disimpan sebagai JSON/Array di database)
                                    $tags = $review->tags ?? [];
                                    if (is_string($tags)) {
                                        $tags = json_decode($tags, true) ?? [];
                                    }
                                @endphp

                                @if ($review)
                                    <div class="flex items-center space-x-2 whitespace-nowrap">
                                        <span class="text-amber-500">
                                            @for ($i = 1; $i <= 5; $i++)
                                                {{ $i <= $rating ? '★' : '☆' }}
                                            @endfor
                                        </span>
                                        <span class="font-semibold text-amber-700">
                                            {{ $rating }}/5
                                        </span>

                                        {{-- Badge Anonim --}}
                                        @if ($review->anonymous)
                                            <span
                                                class="rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-500">Anonim</span>
                                        @endif
                                    </div>

                                    {{-- Komentar ulasan --}}
                                    @if ($comment)
                                        <p class="mt-1 max-w-xs text-stone-600 italic">
                                            "{{ $comment }}"
                                        </p>
                                    @endif

                                    {{-- Badge Tags Ulasan (Penyajian cepat, Harga sepadan, dll) --}}
                                    @if (!empty($tags))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach ($tags as $tag)
                                                <span
                                                    class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800 border border-amber-200">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <span class="italic text-stone-400">
                                        Belum diulas
                                    </span>
                                @endif
                            </td>

                            {{-- Status pembayaran --}}
                            {{-- Status pembayaran / pesanan --}}
                            <td class="px-5 py-5">
                                @php
                                    $status = strtolower($order->status ?? 'pending');

                                    // Mapping label dan warna badge
                                    $statusLabels = [
                                        'completed' => [
                                            'label' => 'SELESAI',
                                            'class' => 'bg-emerald-100 text-emerald-800',
                                        ],
                                        'processing' => ['label' => 'DIPROSES', 'class' => 'bg-blue-100 text-blue-800'],
                                        'cancelled' => ['label' => 'DIBATALKAN', 'class' => 'bg-red-100 text-red-800'],
                                        'pending' => ['label' => 'PENDING', 'class' => 'bg-amber-100 text-amber-800'],
                                    ];

                                    $currentStatus = $statusLabels[$status] ?? [
                                        'label' => strtoupper($status),
                                        'class' => 'bg-stone-100 text-stone-800',
                                    ];
                                @endphp

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
