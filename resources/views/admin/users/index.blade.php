@extends('layout.template')

@section('content')
    @php
        // Mendukung array maupun Eloquent model / stdClass
        $users = collect($users ?? [])
            ->map(fn($u) => is_array($u) ? $u : (method_exists($u, 'toArray') ? $u->toArray() : (array) $u))
            ->values()
            ->all();

        // Owner = role "staff". Dibaca dari user yang login, session hanya cadangan.
        $isOwner = $isOwner ?? (optional(auth()->user())->role ?? session('role')) === 'staff';

        // ID akun yang sedang login (dipakai untuk menyembunyikan tombol Hapus akun sendiri)
        $currentId = (int) auth()->id();

        // Ringkasan
        $totalUsers = count($users);
        $totalOwner = 0;
        $totalAdmin = 0;
        foreach ($users as $u) {
            if (($u['role'] ?? 'admin') === 'staff') {
                $totalOwner++;
            } else {
                $totalAdmin++;
            }
        }
    @endphp

    <style>
        /* Baris tabel muncul bergantian saat halaman dibuka */
        @keyframes bn-row-in {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bn-row {
            animation: bn-row-in .35s ease-out both;
            animation-delay: calc(min(var(--r, 0), 10) * 35ms);
            transition: background-color .15s;
        }

        .bn-row:hover {
            background-color: #fbf8f5;
        }

        .bn-row.is-hidden {
            display: none;
        }

        /* Alert flash */
        .bn-alert {
            transition: opacity .3s, transform .3s;
        }

        .bn-alert.is-closing {
            opacity: 0;
            transform: translateY(-6px);
        }

        /* Fokus keyboard yang jelas */
        .bn-focus:focus-visible {
            outline: 2px solid #754a30;
            outline-offset: 2px;
        }

        /* Tab filter role */
        .bn-tab {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
            padding: .4rem .8rem;
            font-size: .8125rem;
            font-weight: 600;
            color: #78716c;
            transition: background-color .15s, color .15s, box-shadow .15s;
            white-space: nowrap;
        }

        .bn-tab:hover {
            color: #21140e;
        }

        .bn-tab.is-active {
            background: #fff;
            color: #21140e;
            box-shadow: 0 1px 2px rgba(33, 20, 14, .12), 0 0 0 1px rgba(33, 20, 14, .05);
        }

        .bn-tab .bn-tab-count {
            border-radius: 9999px;
            background: #e7e5e4;
            padding: 0 .4rem;
            font-size: .6875rem;
            line-height: 1.1rem;
            color: #57534e;
        }

        .bn-tab.is-active .bn-tab-count {
            background: #754a30;
            color: #fff;
        }

        /* Dialog hapus (native <dialog>: Esc dan fokus sudah ditangani browser) */
        #delete-dialog {
            border: 0;
            padding: 0;
            background: transparent;
            max-width: 100vw;
            max-height: 100vh;
            overflow: visible;
        }

        #delete-dialog::backdrop {
            background: rgba(33, 20, 14, .55);
            backdrop-filter: blur(2px);
        }

        #delete-dialog[open] .bn-dialog-card {
            animation: bn-dialog-in .22s ease-out both;
        }

        @keyframes bn-dialog-in {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .bn-row,
            #delete-dialog[open] .bn-dialog-card {
                animation: none;
            }

            .bn-row,
            .bn-alert,
            .bn-tab {
                transition: none;
            }
        }
    </style>

    <div class="mx-auto w-full max-w-6xl">

        <!-- Header halaman -->
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b9854f]">Manajemen akses</p>
                <h1 class="mt-1.5 font-serif text-3xl font-bold tracking-tight text-[#21140e] md:text-4xl">Partner &amp;
                    User</h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-stone-500">Kelola akun Owner, admin, dan partner
                    yang terhubung dengan BrewNest Coffee.</p>
            </div>
            <a href="{{ url('admin/users/create') }}"
                class="bn-focus inline-flex items-center justify-center gap-2 self-start rounded-xl bg-[#21140e] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#754a30] sm:self-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                Tambah user
            </a>
        </div>

        <!-- Notifikasi -->
        @if (session('success'))
            <div role="status"
                class="bn-alert js-alert mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="flex-1">{{ session('success') }}</span>
                <button type="button"
                    class="js-alert-close bn-focus rounded text-lg leading-none text-emerald-900/50 hover:text-emerald-900"
                    aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div role="alert"
                class="bn-alert js-alert mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.3 3.9L2.4 17.5A2 2 0 004.1 20.5h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" />
                </svg>
                <span class="flex-1">{{ session('error') }}</span>
                <button type="button"
                    class="js-alert-close bn-focus rounded text-lg leading-none text-rose-900/50 hover:text-rose-900"
                    aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif

        <!-- Ringkasan -->
        <dl class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-stone-100 text-stone-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.87M16 2.13a4 4 0 010 7.75" />
                    </svg>
                </span>
                <div>
                    <dt class="text-xs font-medium text-stone-500">Total user</dt>
                    <dd class="text-3xl font-bold tabular-nums leading-tight text-[#21140e]">{{ $totalUsers }}</dd>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#754a30]/10 text-[#754a30]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3l2.6 5.3 5.9.9-4.25 4.1 1 5.8L12 16.3 6.75 19.1l1-5.8L3.5 9.2l5.9-.9L12 3z" />
                    </svg>
                </span>
                <div>
                    <dt class="text-xs font-medium text-stone-500">Owner</dt>
                    <dd class="text-3xl font-bold tabular-nums leading-tight text-[#754a30]">{{ $totalOwner }}</dd>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3l8 3v6c0 4.5-3.2 8.3-8 9-4.8-.7-8-4.5-8-9V6l8-3z" />
                    </svg>
                </span>
                <div>
                    <dt class="text-xs font-medium text-stone-500">Admin &amp; lainnya</dt>
                    <dd class="text-3xl font-bold tabular-nums leading-tight text-[#21140e]">{{ $totalAdmin }}</dd>
                </div>
            </div>
        </dl>

        <!-- Tabel -->
        <div class="w-full overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">

            <!-- Pencarian & filter -->
            <div
                class="flex flex-col gap-4 border-b border-stone-200 p-4 lg:flex-row lg:items-center lg:justify-between lg:px-6">
                <div class="relative w-full lg:max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                    </span>
                    <label for="user-search" class="sr-only">Cari user</label>
                    <input id="user-search" type="search" placeholder="Cari nama, email, atau role"
                        class="bn-focus w-full rounded-xl border border-stone-300 bg-stone-50/60 py-2.5 pl-10 pr-3 text-sm text-stone-800 placeholder:text-stone-400 focus:border-[#754a30] focus:bg-white">
                </div>

                <div class="-mx-1 overflow-x-auto px-1">
                    <div class="inline-flex gap-1 rounded-xl bg-stone-100 p-1" role="group" aria-label="Filter role">
                        <button type="button" class="bn-tab bn-focus js-tab is-active" data-filter="all"
                            aria-pressed="true">Semua <span class="bn-tab-count">{{ $totalUsers }}</span></button>
                        <button type="button" class="bn-tab bn-focus js-tab" data-filter="staff"
                            aria-pressed="false">Owner</button>
                        <button type="button" class="bn-tab bn-focus js-tab" data-filter="admin"
                            aria-pressed="false">Admin</button>
                        <button type="button" class="bn-tab bn-focus js-tab" data-filter="other"
                            aria-pressed="false">Lainnya</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-stone-200 bg-stone-50 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                            <th scope="col" class="w-14 px-4 py-3.5 md:px-6">No</th>
                            <th scope="col" class="px-4 py-3.5 md:px-6">User</th>
                            <th scope="col" class="hidden px-4 py-3.5 md:table-cell md:px-6">Email</th>
                            <th scope="col" class="px-4 py-3.5 md:px-6">Role</th>
                            <th scope="col" class="px-4 py-3.5 text-right md:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="user-body" class="divide-y divide-stone-100 text-sm text-stone-700">
                        @forelse ($users as $user)
                            @php
                                $displayName = $user['username'] ?? ($user['name'] ?? 'Admin');
                                $roleKey = $user['role'] ?? 'admin';
                                $roleLabel = $roleKey === 'staff' ? 'Owner' : ucfirst($roleKey ?: 'Administrator');
                                $roleGroup = in_array($roleKey, ['staff', 'admin'], true) ? $roleKey : 'other';
                                $roleClass = match ($roleKey) {
                                    'staff' => 'bg-[#754a30]/10 text-[#754a30] ring-[#754a30]/20',
                                    'admin' => 'bg-amber-50 text-amber-800 ring-amber-200',
                                    default => 'bg-stone-100 text-stone-700 ring-stone-200',
                                };
                                $dotClass = match ($roleKey) {
                                    'staff' => 'bg-[#754a30]',
                                    'admin' => 'bg-amber-500',
                                    default => 'bg-stone-400',
                                };
                                $searchText = mb_strtolower(
                                    $displayName . ' ' . ($user['email'] ?? '') . ' ' . $roleLabel,
                                );

                                // Akun yang sedang login
                                $isSelf = (int) ($user['id'] ?? 0) === $currentId;

                                // Owner boleh mengelola semua; selain Owner tidak boleh menyentuh akun Owner
                                $canManage = $isOwner || $roleKey !== 'staff';

                                $photoFile = !empty($user['photo']) ? basename($user['photo']) : null;
                                $hasPhoto = $photoFile && is_file(public_path('uploads/users/' . $photoFile));
                            @endphp
                            <tr class="bn-row js-row" style="--r:{{ $loop->index }}" data-search="{{ $searchText }}"
                                data-role="{{ $roleGroup }}">
                                <td class="px-4 py-4 tabular-nums text-stone-400 md:px-6">{{ $loop->iteration }}</td>

                                <!-- Foto + nama (+ email di layar kecil) -->
                                <td class="px-4 py-4 md:px-6">
                                    <div class="flex items-center gap-3.5">
                                        @if ($hasPhoto)
                                            <img src="{{ asset('uploads/users/' . $photoFile) }}"
                                                alt="Foto {{ $displayName }}"
                                                class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-white shadow">
                                        @else
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#d9a46a] to-[#754a30] text-sm font-bold text-white ring-2 ring-white shadow"
                                                aria-hidden="true">
                                                {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-2 font-semibold text-[#21140e]">
                                                <span class="truncate">{{ $displayName }}</span>
                                                @if ($isSelf)
                                                    <span
                                                        class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-inset ring-emerald-200">Anda</span>
                                                @endif
                                            </p>
                                            <p class="truncate text-xs text-stone-500 md:hidden">
                                                {{ $user['email'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="hidden px-4 py-4 text-stone-600 md:table-cell md:px-6">
                                    {{ $user['email'] ?? '-' }}</td>

                                <td class="px-4 py-4 md:px-6">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $roleClass }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $roleLabel }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 md:px-6">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($canManage)
                                            <a href="{{ url('admin/users/' . $user['id'] . '/edit') }}"
                                                class="bn-focus inline-flex items-center gap-1.5 rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 transition hover:border-[#754a30] hover:text-[#754a30]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                                Edit
                                            </a>

                                            @if ($isSelf)
                                                {{-- Akun sendiri: tombol Hapus tidak aktif --}}
                                                <span aria-disabled="true"
                                                    title="Kamu tidak bisa menghapus akunmu sendiri"
                                                    class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-stone-200 bg-stone-50 px-3 py-1.5 text-xs font-semibold text-stone-400">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 6h18M8 6V4h8v2m-9 0l1 14h8l1-14" />
                                                    </svg>
                                                    Hapus
                                                </span>
                                            @else
                                                <button type="button"
                                                    data-action="{{ url('admin/users/' . $user['id']) }}"
                                                    data-name="{{ $displayName }}"
                                                    class="js-delete bn-focus inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-600 hover:text-white">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M3 6h18M8 6V4h8v2m-9 0l1 14h8l1-14" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs italic text-stone-400">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16 11V7a4 4 0 00-8 0v4M6 11h12v9H6z" />
                                                </svg>
                                                Khusus Owner
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <span
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 text-stone-400">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8z" />
                                        </svg>
                                    </span>
                                    <p class="mt-4 font-semibold text-[#21140e]">Belum ada user terdaftar</p>
                                    <p class="mt-1 text-sm text-stone-500">Tambahkan akun pertama untuk mulai
                                        mengelola partner dan admin.</p>
                                    <a href="{{ url('admin/users/create') }}"
                                        class="bn-focus mt-5 inline-block rounded-xl bg-[#21140e] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#754a30]">Tambah
                                        user pertama</a>
                                </td>
                            </tr>
                        @endforelse

                        <!-- Muncul saat pencarian/filter tidak menemukan hasil -->
                        <tr id="no-result" class="hidden">
                            <td colspan="5" class="px-6 py-14 text-center">
                                <span
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                    </svg>
                                </span>
                                <p class="mt-3 font-semibold text-[#21140e]">Tidak ada user yang cocok</p>
                                <p class="mt-1 text-sm text-stone-500">Coba kata kunci lain atau ubah filter role.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer tabel -->
            <div class="flex items-center justify-between border-t border-stone-200 bg-stone-50 px-4 py-3 text-xs text-stone-500 md:px-6"
                aria-live="polite">
                <p>
                    Menampilkan <span id="visible-count"
                        class="font-semibold tabular-nums text-[#21140e]">{{ $totalUsers }}</span> dari
                    <span class="tabular-nums">{{ $totalUsers }}</span> user
                </p>
            </div>
        </div>

    </div>

    <!-- Dialog konfirmasi hapus -->
    <dialog id="delete-dialog" aria-labelledby="delete-title">
        <div class="bn-dialog-card mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 6h18M8 6V4h8v2m-9 0l1 14h8l1-14M10 11v6m4-6v6" />
                </svg>
            </div>
            <h2 id="delete-title" class="mt-4 text-lg font-bold text-[#21140e]">Hapus user ini?</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-stone-500">Akun <strong id="delete-name"
                    class="text-[#21140e]"></strong> akan dihapus permanen dan tidak bisa dikembalikan.</p>

            <form id="delete-form" method="POST" action="#" class="mt-6 flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" id="delete-cancel"
                    class="bn-focus flex-1 rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-100">Batal</button>
                <button type="submit" id="delete-confirm"
                    class="bn-focus flex-1 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60">Hapus
                    user</button>
            </form>
        </div>
    </dialog>

    <script>
        (function() {
            // ===== Notifikasi: tutup manual + hilang otomatis =====
            function closeAlert(el) {
                el.classList.add('is-closing');
                setTimeout(function() {
                    el.remove();
                }, 320);
            }
            document.querySelectorAll('.js-alert').forEach(function(el) {
                var closeBtn = el.querySelector('.js-alert-close');
                if (closeBtn) closeBtn.addEventListener('click', function() {
                    closeAlert(el);
                });
                setTimeout(function() {
                    closeAlert(el);
                }, 6000);
            });

            // ===== Pencarian + filter role =====
            var search = document.getElementById('user-search');
            var tabs = Array.prototype.slice.call(document.querySelectorAll('.js-tab'));
            var rows = Array.prototype.slice.call(document.querySelectorAll('.js-row'));
            var noResult = document.getElementById('no-result');
            var visibleCount = document.getElementById('visible-count');
            var activeRole = 'all';

            function applyFilter() {
                var q = search.value.trim().toLowerCase();
                var shown = 0;

                rows.forEach(function(row) {
                    var matchText = row.dataset.search.indexOf(q) !== -1;
                    var matchRole = activeRole === 'all' || row.dataset.role === activeRole;
                    var match = matchText && matchRole;
                    row.classList.toggle('is-hidden', !match);
                    if (match) shown++;
                });

                visibleCount.textContent = shown;
                noResult.classList.toggle('hidden', shown !== 0 || rows.length === 0);
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    activeRole = tab.dataset.filter;
                    tabs.forEach(function(t) {
                        var on = t === tab;
                        t.classList.toggle('is-active', on);
                        t.setAttribute('aria-pressed', String(on));
                    });
                    applyFilter();
                });
            });

            search.addEventListener('input', applyFilter);

            // ===== Dialog hapus (DELETE dikirim lewat form + CSRF) =====
            var dialog = document.getElementById('delete-dialog');
            var nameEl = document.getElementById('delete-name');
            var deleteForm = document.getElementById('delete-form');
            var confirmBtn = document.getElementById('delete-confirm');
            var cancelBtn = document.getElementById('delete-cancel');
            var confirmLabel = confirmBtn.textContent;

            function resetConfirm() {
                confirmBtn.disabled = false;
                confirmBtn.textContent = confirmLabel;
            }

            function openDialog(action, name) {
                nameEl.textContent = name;
                deleteForm.action = action;
                resetConfirm();
                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    // Cadangan untuk browser lama yang belum mendukung <dialog>
                    if (window.confirm('Hapus akun ' + name + '? Tindakan ini tidak bisa dibatalkan.')) {
                        deleteForm.submit();
                    }
                    return;
                }
                cancelBtn.focus();
            }

            document.querySelectorAll('.js-delete').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openDialog(btn.dataset.action, btn.dataset.name);
                });
            });

            cancelBtn.addEventListener('click', function() {
                dialog.close();
            });

            // Klik di area gelap (backdrop) menutup dialog
            dialog.addEventListener('click', function(e) {
                if (e.target === dialog) dialog.close();
            });

            deleteForm.addEventListener('submit', function() {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Menghapus...';
            });

            // Tombol "Back" browser: pastikan tombol tidak tertinggal dalam keadaan "Menghapus..."
            window.addEventListener('pageshow', function() {
                resetConfirm();
                if (dialog.open) dialog.close();
            });
        })();
    </script>
@endsection
