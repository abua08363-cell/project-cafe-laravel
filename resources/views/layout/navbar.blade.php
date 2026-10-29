@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\User;

    $currentUser = Auth::user();
    if (!$currentUser && session()->has('user_id')) {
        $currentUser = User::find(session('user_id'));
    }

    $nvUsername = $currentUser->username ?? (session('username') ?? (session('name') ?? 'Admin'));
    $nvRole = $currentUser->role ?? (session('role') ?? 'admin');
    $nvRoleText = $nvRole === 'staff' ? 'Owner' : ucfirst($nvRole);
    $nvPhoto = $currentUser->photo ?? (session('photo') ?? null);

    $nvHasPhoto = $nvPhoto && file_exists(public_path('uploads/users/' . $nvPhoto));
    $nvInitial = strtoupper(mb_substr($nvUsername, 0, 1));
@endphp

<header
    class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-stone-200/80 bg-[#fbf8f3]/95 px-4 shadow-sm backdrop-blur sm:px-6">

    {{-- Kiri: hamburger + judul --}}
    <div class="flex min-w-0 items-center gap-3">
        {{-- Muncul di HP & tablet (lg:hidden), hilang di laptop --}}
        <button type="button" onclick="toggleSidebar()" aria-label="Buka menu"
            class="flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-stone-300 bg-white text-stone-700 shadow-sm transition hover:bg-stone-100 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-600 focus-visible:ring-offset-2 lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h10" />
            </svg>
        </button>

        <div class="min-w-0">
            <h1 class="truncate font-serif text-base font-bold leading-tight text-stone-900 sm:text-lg">
                Admin Dashboard
            </h1>
            {{-- Tanggal hanya di layar ≥ sm --}}
            <p id="nvD" class="mt-0.5 hidden text-[11px] leading-none text-stone-500 sm:block"></p>
        </div>
    </div>

    {{-- Kanan: jam + profil --}}
    <div class="flex flex-none items-center gap-2.5 sm:gap-4">

        {{-- Jam WIB: tampil di semua ukuran, "WIB" disembunyikan di HP --}}
        <div
            class="flex items-center gap-1.5 rounded-full border border-stone-200 bg-white px-2.5 py-1.5 text-xs shadow-sm sm:gap-2 sm:px-3">
            <span aria-hidden="true">🕒</span>
            <span class="font-bold tabular-nums text-stone-800">
                <span id="nvH">--</span>:<span id="nvM">--</span><span class="hidden sm:inline"> WIB</span>
            </span>
        </div>

        {{-- Profil --}}
        <div class="flex items-center gap-2.5">
            <div class="hidden text-right sm:block">
                <p class="max-w-[10rem] truncate text-xs font-bold leading-tight text-stone-900">{{ $nvUsername }}</p>
                <span
                    class="mt-0.5 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-bold text-amber-800">{{ $nvRoleText }}</span>
            </div>

            <div title="{{ $nvUsername }} · {{ $nvRoleText }}"
                class="flex h-9 w-9 flex-none items-center justify-center overflow-hidden rounded-full border-2 border-amber-600 bg-amber-100 text-sm font-bold text-amber-800 shadow-sm">
                @if ($nvHasPhoto)
                    <img src="{{ asset('uploads/users/' . $nvPhoto) }}" alt="Foto profil {{ $nvUsername }}"
                        class="h-full w-full object-cover">
                @else
                    {{ $nvInitial }}
                @endif
            </div>
        </div>
    </div>
</header>

<script>
    (function() {
        const hEl = document.getElementById('nvH');
        const mEl = document.getElementById('nvM');
        const dEl = document.getElementById('nvD');
        if (!hEl) return;

        const timeFmt = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        const dateFmt = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        function tick() {
            const now = new Date();
            const parts = timeFmt.formatToParts(now);
            hEl.textContent = parts.find(p => p.type === 'hour').value;
            mEl.textContent = parts.find(p => p.type === 'minute').value;
            if (dEl) dEl.textContent = dateFmt.format(now);
        }
        tick();
        setInterval(tick, 15000);
    })();
</script>
