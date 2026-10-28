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
    class="sticky top-0 z-30 flex h-16 items-center justify-between px-6 bg-[#fbf8f3]/95 backdrop-blur border-b border-stone-200/80 shadow-sm">
    <div class="flex items-center gap-3">
        <!-- Tombol Garis Tiga MUNCUL di HP & Tablet (lg:hidden), HILANG di Laptop -->
        <button type="button"
            class="flex lg:hidden items-center justify-center w-9 h-9 rounded-lg bg-white border border-stone-300 text-stone-700 hover:bg-stone-100 transition shadow-sm"
            onclick="toggleSidebar()" aria-label="Buka Menu">
            ☰
        </button>

        <div>
            <h1 class="font-serif text-base sm:text-lg font-bold text-stone-900">Admin Dashboard</h1>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <!-- Jam WIB -->
        <div
            class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-stone-200 text-xs shadow-sm">
            <span>🕒</span>
            <span class="font-bold text-stone-800"><span id="nvH">--</span>:<span id="nvM">--</span>
                WIB</span>
        </div>

        <!-- Profil -->
        <div class="flex items-center gap-2.5">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold text-stone-900 leading-tight">{{ $nvUsername }}</p>
                <span
                    class="inline-block px-2 py-0.5 mt-0.5 text-[9px] font-bold rounded-full bg-amber-100 text-amber-800">{{ $nvRoleText }}</span>
            </div>

            <div
                class="w-9 h-9 rounded-full overflow-hidden border-2 border-amber-600 bg-amber-100 flex items-center justify-center font-bold text-amber-800 text-sm shadow-sm flex-shrink-0">
                @if ($nvHasPhoto)
                    <img src="{{ asset('uploads/users/' . $nvPhoto) }}" alt="Foto Profil"
                        class="w-full h-full object-cover">
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
        if (!hEl) return;

        const timeFmt = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        function tick() {
            const parts = timeFmt.formatToParts(new Date());
            hEl.textContent = parts.find(p => p.type === 'hour').value;
            mEl.textContent = parts.find(p => p.type === 'minute').value;
        }
        tick();
        setInterval(tick, 15000);
    })();
</script>
