<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Admin BrewNest' }}</title>

    <!-- Favicon Logo Kafe -->
    <link rel="icon" type="image/jpeg" href="{{ asset('kopi.jpeg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-stone-100">

    <div class="flex min-h-screen">

        {{-- Include Sidebar --}}
        @include('layout.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Include Navbar --}}
            @include('layout.navbar')

            <main class="flex-1 p-6">
                {{-- Bagian Konten Utama yang akan diisi oleh halaman lain --}}
                @yield('content')
            </main>

            {{-- Include Bottom Navigation --}}
            @include('layout.bottomav')

        </div>

    </div>

</body>

</html>
