<?php
$sbItems = [['admin/dashboard', 'admin.dashboard', '🏠', 'Home page'], ['admin/menu', 'admin.menu.index', '☕', 'Menu'], ['admin/users', 'admin.users.index', '👥', 'Partners'], ['admin/categories', 'admin.categories.index', '🗂️', 'Categories'], ['admin/settings', 'admin.settings.index', '⚙️', 'Settings'], ['admin/orders', 'admin.orders.index', '🕒', 'History']];
?>

<style>
    .sb-aside-custom {
        background: radial-gradient(120% 60% at 0% 0%, rgba(185, 133, 79, .22), transparent 60%),
            radial-gradient(90% 50% at 100% 100%, rgba(117, 74, 48, .28), transparent 60%),
            #1c120c;
    }

    .sb-cup {
        position: relative;
        display: grid;
        place-items: center;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        font-size: 24px;
        background: linear-gradient(145deg, #754a30, #3b2416);
        box-shadow: 0 8px 20px -8px rgba(185, 133, 79, .7);
    }

    .sb-brand {
        background: linear-gradient(90deg, #fff, #f3d9b1, #fff);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .sb-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .65rem .9rem;
        border-radius: .9rem;
        font-size: .92rem;
        font-weight: 500;
        color: #d6d3d1;
        transition: all .25s ease;
    }

    .sb-link:hover {
        color: #fff;
        background: rgba(185, 133, 79, .15);
        transform: translateX(4px);
    }

    .sb-link.is-active {
        color: #fff;
        font-weight: 600;
        background: linear-gradient(100deg, #8a5636 0%, #754a30 45%, #5c3822 100%);
        box-shadow: 0 10px 24px -10px rgba(185, 133, 79, .75);
    }

    .sb-ico {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: .7rem;
        background: rgba(255, 255, 255, .05);
    }

    .sb-dot {
        margin-left: auto;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #f3d9b1;
    }

    .sb-logout {
        display: flex;
        width: 100%;
        align-items: center;
        gap: .75rem;
        padding: .65rem .9rem;
        border-radius: .9rem;
        font-size: .88rem;
        color: #a8a29e;
        background: transparent;
        transition: all .25s;
    }

    .sb-logout:hover {
        color: #fecaca;
        background: rgba(239, 68, 68, .12);
    }
</style>

<!-- Overlay Mobile & Tablet (< 1024px) -->
<div id="sbOverlay"
    class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"
    onclick="toggleSidebar()"></div>

<!-- Menggunakan 'hidden lg:flex' agar Tablet & HP tersembunyi, Laptop saja yang tampil permanen -->
<aside id="sbAside"
    class="sb-aside-custom fixed lg:sticky top-0 z-50 h-screen w-64 shrink-0 flex-col border-r border-[#2d1e15] text-stone-200 -translate-x-full lg:translate-x-0 transition-transform duration-300 flex">

    <!-- Logo -->
    <a href="{{ url('admin/dashboard') }}" class="flex items-center gap-3 border-b border-[#2d1e15] px-6 py-6">
        <span class="sb-cup">☕</span>
        <div>
            <h1 class="sb-brand text-xl font-bold leading-none">BrewNest</h1>
            <p class="mt-1.5 text-[10px] font-semibold uppercase tracking-[0.25em] text-amber-500/80">Coffee Admin</p>
        </div>
    </a>

    <!-- Menu Navigasi -->
    <nav class="flex-1 space-y-1.5 overflow-y-auto px-4 py-6">
        <p class="mb-3 px-2 text-[10px] font-bold uppercase tracking-[0.25em] text-stone-500">Navigasi</p>

        @foreach ($sbItems as [$url, $routeName, $icon, $label])
            @php $active = request()->routeIs($routeName); @endphp
            <a href="{{ url($url) }}" class="sb-link {{ $active ? 'is-active' : '' }}">
                <span class="sb-ico">{{ $icon }}</span>
                <span>{{ $label }}</span>
                @if ($active)
                    <span class="sb-dot"></span>
                @endif
            </a>
        @endforeach
    </nav>

    <!-- Logout -->
    <div class="border-t border-[#2d1e15] p-4">
        <form action="{{ url('logout') }}" method="post">
            @csrf
            <button type="submit" class="sb-logout">
                <span>🚪</span> Log out
            </button>
        </form>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const aside = document.getElementById('sbAside');
        const overlay = document.getElementById('sbOverlay');
        if (aside && overlay) {
            aside.classList.toggle('-translate-x-full');
            overlay.classList.toggle('opacity-0');
            overlay.classList.toggle('pointer-events-none');
        }
    }
</script>
