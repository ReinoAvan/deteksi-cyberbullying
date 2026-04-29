<!DOCTYPE html>
<html>
<head>
    <title>SafeSpace</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR (PINDAH KE SINI) -->
    <aside class="w-64 bg-gradient-to-b from-indigo-600 to-purple-600 text-white p-5 space-y-6">

        <div>
            <h1 class="text-xl font-bold">🛡️ SafeSpace</h1>
            <p class="text-xs opacity-70">Cyberbullying Monitor</p>
        </div>

        <nav class="space-y-2">

            <a href="/" class="block p-2 rounded-lg {{ request()->is('/') ? 'bg-white/20' : 'opacity-80 hover:bg-white/10' }}">
                🏠 Dashboard
            </a>

            <a href="#" class="block opacity-80 hover:bg-white/10 p-2 rounded-lg">
                📊 Monitoring Real-time
            </a>

            <a href="#" class="block opacity-80 hover:bg-white/10 p-2 rounded-lg">
                📄 Laporan & Kasus
            </a>

            <a href="#" class="block opacity-80 hover:bg-white/10 p-2 rounded-lg">
                📈 Analisis Perilaku
            </a>

            <a href="/manajemen-siswa"
               class="block p-2 rounded-lg {{ request()->is('manajemen-siswa') ? 'bg-white/20' : 'opacity-80 hover:bg-white/10' }}">
               👥 Manajemen Siswa
            </a>

        </nav>

        <div class="mt-auto bg-white/10 p-4 rounded-xl text-sm">
            <p class="font-semibold">AI Protection aktif</p>
            <p class="text-xs opacity-70">Monitoring real-time</p>
        </div>

    </aside>

    <!-- CONTENT -->
    <main class="flex-1 p-6">
        {{ $slot }}
    </main>

</div>

@livewireScripts
</body>
</html>