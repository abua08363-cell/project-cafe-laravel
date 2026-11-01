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
            'completed' => [
                'label' => 'Selesai',
                'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'dot' => 'bg-emerald-500',
            ],
            'processing' => [
                'label' => 'Diproses',
                'class' => 'bg-blue-50 text-blue-700 ring-blue-200',
                'dot' => 'bg-blue-500',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'class' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'dot' => 'bg-rose-500',
            ],
            'pending' => [
                'label' => 'Pending',
                'class' => 'bg-amber-50 text-amber-800 ring-amber-200',
                'dot' => 'bg-amber-500',
            ],
        ];
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-8">

        {{-- Judul & Sambutan --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b9854f]">BrewNest Coffee</p>
            <h1 class="mt-1.5 font-serif text-3xl font-bold tracking-tight text-[#21140e] md:text-4xl">Riwayat Pesanan
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-stone-500">
                Arsip seluruh transaksi dan pesanan yang telah lunas / selesai disajikan.
            </p>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Total Transaksi --}}
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#754a30]/10 text-[#754a30]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6M9 16h4" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-medium text-stone-500">Total Transaksi</p>
                    <h3 class="text-2xl font-bold tabular-nums leading-tight text-[#21140e]">{{ $totalTransaksi ?? 0 }}
                    </h3>
                </div>
            </div>

            {{-- Total Pendapatan --}}
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7h18v10H3zM12 14a2 2 0 100-4 2 2 0 000 4zM6 10v.01M18 14v.01" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-medium text-stone-500">Total Pendapatan</p>
                    <h3 class="text-2xl font-bold tabular-nums leading-tight text-[#21140e]">Rp
                        {{ number_format((float) ($totalPendapatan ?? 0), 0, ',', '.') }}</h3>
                </div>
            </div>

            {{-- Rata-rata Rating --}}
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9L12 2.5z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-medium text-stone-500">Rata-rata Rating
                        ({{ $totalReviewCount ?? 0 }} ulasan)</p>
                    <h3 class="text-2xl font-bold tabular-nums leading-tight text-[#21140e]">
                        {{ number_format((float) ($avgRating ?? 0), 1) }}<span
                            class="text-base font-semibold text-stone-400"> / 5</span>
                    </h3>
                </div>
            </div>
        </div>

        {{-- Notifikasi --}}
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

        {{-- Tabel Riwayat Pesanan --}}
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4 md:px-6">
                <h2 class="font-semibold text-[#21140e]">Daftar Transaksi</h2>
                <span
                    class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold tabular-nums text-stone-600">{{ $orders->count() }}
                    pesanan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left text-sm">

                    <thead>
                        <tr
                            class="border-b border-stone-200 bg-stone-50 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                            <th class="px-5 py-3.5 md:px-6">No. Meja</th>
                            <th class="px-5 py-3.5">Nama Pemesan</th>
                            <th class="px-5 py-3.5">Item Menu</th>
                            <th class="px-5 py-3.5">Total Bayar</th>
                            <th class="px-5 py-3.5">Ulasan &amp; Rating</th>
                            <th class="px-5 py-3.5">Status Pembayaran</th>
                            <th class="px-5 py-3.5 text-right md:px-6">Aksi</th>
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
                                    'label' => ucfirst($status),
                                    'class' => 'bg-stone-100 text-stone-700 ring-stone-200',
                                    'dot' => 'bg-stone-400',
                                ];
                            @endphp

                            <tr class="align-top transition-colors hover:bg-[#fbf8f5]">

                                {{-- Nomor meja --}}
                                <td class="px-5 py-5 md:px-6">
                                    <span
                                        class="inline-flex min-w-[2.5rem] justify-center rounded-lg bg-[#754a30]/10 px-3 py-1.5 text-sm font-bold text-[#754a30]">
                                        {{ $order->table_number ?? ($order->table_no ?? '-') }}
                                    </span>
                                </td>

                                {{-- Nama pemesan --}}
                                <td class="px-5 py-5">
                                    <p class="font-semibold text-[#21140e]">{{ $order->customer_name }}</p>
                                </td>

                                {{-- Item menu (+ bintang per menu kalau ada) --}}
                                <td class="px-5 py-5">
                                    @forelse ($order->items as $item)
                                        @php
                                            $menuStar = $itemRatings[$item->menu_id] ?? null;
                                        @endphp
                                        <p class="mb-1 text-stone-700 last:mb-0">
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
                                <td class="whitespace-nowrap px-5 py-5 font-bold tabular-nums text-[#21140e]">
                                    Rp {{ number_format((float) $order->total_price, 0, ',', '.') }}
                                </td>

                                {{-- Ulasan dan rating --}}
                                <td class="px-5 py-5">
                                    @if ($review)
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <span class="tracking-wider text-amber-500">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    {{ $i <= $rating ? '★' : '☆' }}
                                                @endfor
                                            </span>
                                            <span class="font-semibold text-amber-700">{{ $rating }}/5</span>

                                            @if ($review->anonymous)
                                                <span
                                                    class="rounded bg-stone-100 px-1.5 py-0.5 text-[11px] font-medium text-stone-500">Anonim</span>
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
                                                        <span class="font-semibold text-amber-600">★
                                                            {{ $val }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Komentar --}}
                                        @if ($comment)
                                            <p
                                                class="mt-2 max-w-xs border-l-2 border-[#d9a46a] pl-3 text-sm italic leading-relaxed text-stone-600">
                                                "{{ $comment }}"</p>
                                        @endif

                                        {{-- Tags --}}
                                        @if (!empty($tags))
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach ($tags as $tag)
                                                    <span
                                                        class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                                        {{ $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-sm italic text-stone-400">Belum diulas</span>
                                    @endif
                                </td>

                                {{-- Status pesanan --}}
                                <td class="px-5 py-5">
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $currentStatus['class'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $currentStatus['dot'] }}"></span>
                                        {{ $currentStatus['label'] }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-5 text-right md:px-6">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-[#21140e] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#754a30] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#754a30]">
                                        Lihat Nota
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <span
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 text-stone-400">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </span>
                                    <p class="mt-4 font-semibold text-[#21140e]">Belum ada pesanan</p>
                                    <p class="mt-1 text-sm text-stone-500">Transaksi yang selesai akan muncul di sini.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

    </div>
@endsection
