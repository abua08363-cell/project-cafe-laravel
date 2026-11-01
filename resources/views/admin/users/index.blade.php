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
        /* Satu gerakan saat halaman dibuka: baris tabel muncul bergantian */
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
            background-color: #faf6f3;
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
            .bn-alert {
                transition: none;
            }
        }
    </style>

    <div class="w-full">

        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 md:text-3xl">Partner &amp; User</h1>
                <p class="mt-1 max-w-xl text-sm text-stone-500">Kelola akun Owner, admin, dan partner yang terhubung
                    dengan BrewNest Coffee.</p>
            </div>
            <a href="{{ url('admin/users/create') }}"
                class="bn-focus inline-flex items-center gap-2 self-start rounded-lg bg-[#754a30] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#21140e] sm:self-auto">
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
                class="bn-alert js-alert mb-4 flex items-start justify-between gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                <span>{{ session('success') }}</span>
                <button type="button"
                    class="js-alert-close bn-focus text-lg leading-none text-green-800/60 hover:text-green-900"
                    aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div role="alert"
                class="bn-alert js-alert mb-4 flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <span>{{ session('error') }}</span>
                <button type="button"
                    class="js-alert-close bn-focus text-lg leading-none text-red-800/60 hover:text-red-900"
                    aria-label="Tutup notifikasi">&times;</button>
            </div>
        @endif

        <!-- Ringkasan: satu bar, bukan tiga kartu -->
        <dl class="mb-6 grid grid-cols-3 divide-x divide-stone-200 rounded-xl border border-stone-200 bg-white">
            <div class="px-4 py-4 sm:px-6">
                <dt class="text-xs text-stone-500">Total user</dt>
                <dd class="mt-1 text-2xl font-bold tabular-nums text-stone-900 sm:text-3xl">{{ $totalUsers }}</dd>
            </div>
            <div class="px-4 py-4 sm:px-6">
                <dt class="text-xs text-stone-500">Owner</dt>
                <dd class="mt-1 text-2xl font-bold tabular-nums text-[#754a30] sm:text-3xl">{{ $totalOwner }}</dd>
            </div>
            <div class="px-4 py-4 sm:px-6">
                <dt class="text-xs text-stone-500">Admin &amp; lainnya</dt>
                <dd class="mt-1 text-2xl font-bold tabular-nums text-stone-900 sm:text-3xl">{{ $totalAdmin }}</dd>
            </div>
        </dl>

        <!-- Tabel -->
        <div class="w-full overflow-hidden rounded-xl border border-stone-200 bg-white">

            <!-- Pencarian & filter -->
            <div
                class="flex flex-col gap-3 border-b border-stone-200 p-4 md:flex-row md:items-center md:justify-between md:px-6">
                <div class="flex w-full flex-col gap-3 sm:flex-row md:max-w-xl">
                    <div class="relative w-full">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                            </svg>
                        </span>
                        <label for="user-search" class="sr-only">Cari user</label>
                        <input id="user-search" type="search" placeholder="Cari nama, email, atau role"
                            class="bn-focus w-full rounded-lg border border-stone-300 py-2 pl-9 pr-3 text-sm text-stone-800 placeholder:text-stone-400 focus:border-[#754a30]">
                    </div>
                    <div class="sm:w-44">
                        <label for="role-filter" class="sr-only">Filter role</label>
                        <select id="role-filter"
                            class="bn-focus w-full rounded-lg border border-stone-300 bg-white py-2 pl-3 pr-8 text-sm text-stone-800 focus:border-[#754a30]">
                            <option value="all">Semua role</option>
                            <option value="staff">Owner</option>
                            <option value="admin">Admin</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                </div>
                <p class="text-xs text-stone-500" aria-live="polite">
                    Menampilkan <span id="visible-count"
                        class="font-semibold tabular-nums text-stone-800">{{ $totalUsers }}</span> dari
                    {{ $totalUsers }} user
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-xs font-medium text-stone-500">
                            <th scope="col" class="w-14 px-4 py-3 md:px-6">No</th>
                            <th scope="col" class="px-4 py-3 md:px-6">User</th>
                            <th scope="col" class="hidden px-4 py-3 md:table-cell md:px-6">Email</th>
                            <th scope="col" class="px-4 py-3 md:px-6">Role</th>
                            <th scope="col" class="px-4 py-3 text-right md:px-6">Aksi</th>
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
                                    'staff' => 'bg-[#754a30] text-white',
                                    'admin' => 'bg-amber-100 text-[#754a30]',
                                    default => 'bg-stone-100 text-stone-700',
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
                                <td class="px-4 py-3 tabular-nums text-stone-500 md:px-6">{{ $loop->iteration }}</td>

                                <!-- Foto + nama (+ email di layar kecil) -->
                                <td class="px-4 py-3 md:px-6">
                                    <div class="flex items-center gap-3">
                                        @if ($hasPhoto)
                                            <img src="{{ asset('uploads/users/' . $photoFile) }}"
                                                alt="Foto {{ $displayName }}"
                                                class="h-10 w-10 shrink-0 rounded-full border border-stone-200 object-cover">
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-amber-200 bg-amber-50 text-sm font-bold text-[#754a30]"
                                                aria-hidden="true">
                                                {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-2 font-medium text-stone-900">
                                                <span class="truncate">{{ $displayName }}</span>
                                                @if ($isSelf)
                                                    <span
                                                        class="shrink-0 rounded bg-stone-100 px-1.5 py-0.5 text-[11px] font-medium text-stone-600">Anda</span>
                                                @endif
                                            </p>
                                            <p class="truncate text-xs text-stone-500 md:hidden">
                                                {{ $user['email'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="hidden px-4 py-3 md:table-cell md:px-6">{{ $user['email'] ?? '-' }}</td>

                                <td class="px-4 py-3 md:px-6">
                                    <span
                                        class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleClass }}">{{ $roleLabel }}</span>
                                </td>

                                <td class="px-4 py-3 md:px-6">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($canManage)
                                            <a href="{{ url('admin/users/' . $user['id'] . '/edit') }}"
                                                class="bn-focus rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 transition-colors hover:bg-stone-100">
                                                Edit
                                            </a>

                                            @if ($isSelf)
                                                {{-- Akun sendiri: tombol Hapus tidak dirender --}}
                                                <span aria-disabled="true"
                                                    title="Kamu tidak bisa menghapus akunmu sendiri"
                                                    class="cursor-not-allowed rounded-lg border border-stone-200 bg-stone-50 px-3 py-1.5 text-xs font-semibold text-stone-400">
                                                    Hapus
                                                </span>
                                            @else
                                                <button type="button"
                                                    data-action="{{ url('admin/users/' . $user['id']) }}"
                                                    data-name="{{ $displayName }}"
                                                    class="js-delete bn-focus rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-rose-700">
                                                    Hapus
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-xs italic text-stone-400">Khusus Owner</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <p class="font-medium text-stone-800">Belum ada user terdaftar</p>
                                    <p class="mt-1 text-sm text-stone-500">Tambahkan akun pertama untuk mulai
                                        mengelola partner dan admin.</p>
                                    <a href="{{ url('admin/users/create') }}"
                                        class="bn-focus mt-4 inline-block rounded-lg bg-[#754a30] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#21140e]">Tambah
                                        user pertama</a>
                                </td>
                            </tr>
                        @endforelse

                        <!-- Muncul saat pencarian/filter tidak menemukan hasil -->
                        <tr id="no-result" class="hidden">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="font-medium text-stone-800">Tidak ada user yang cocok</p>
                                <p class="mt-1 text-sm text-stone-500">Coba kata kunci lain atau ubah filter role.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Dialog konfirmasi hapus -->
    <dialog id="delete-dialog" aria-labelledby="delete-title">
        <div class="bn-dialog-card mx-4 w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 6h18M8 6V4h8v2m-9 0l1 14h8l1-14M10 11v6m4-6v6" />
                </svg>
            </div>
            <h2 id="delete-title" class="mt-4 text-lg font-bold text-stone-900">Hapus user ini?</h2>
            <p class="mt-1 text-sm text-stone-500">Akun <strong id="delete-name" class="text-stone-900"></strong>
                akan dihapus permanen dan tidak bisa dikembalikan.</p>

            <form id="delete-form" method="POST" action="#" class="mt-6 flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" id="delete-cancel"
                    class="bn-focus flex-1 rounded-lg border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 transition-colors hover:bg-stone-100">Batal</button>
                <button type="submit" id="delete-confirm"
                    class="bn-focus flex-1 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60">Hapus
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
            var roleFilter = document.getElementById('role-filter');
            var rows = Array.prototype.slice.call(document.querySelectorAll('.js-row'));
            var noResult = document.getElementById('no-result');
            var visibleCount = document.getElementById('visible-count');

            function applyFilter() {
                var q = search.value.trim().toLowerCase();
                var role = roleFilter.value;
                var shown = 0;

                rows.forEach(function(row) {
                    var matchText = row.dataset.search.indexOf(q) !== -1;
                    var matchRole = role === 'all' || row.dataset.role === role;
                    var match = matchText && matchRole;
                    row.classList.toggle('is-hidden', !match);
                    if (match) shown++;
                });

                visibleCount.textContent = shown;
                noResult.classList.toggle('hidden', shown !== 0 || rows.length === 0);
            }

            search.addEventListener('input', applyFilter);
            roleFilter.addEventListener('change', applyFilter);

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
