<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Monitoring</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-slate-100">

@php
    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'line' => 'M3 12l9-9 9 9M5 10v10h14V10',
            'solid' => 'M12 3 3 11h2v9h5v-6h4v6h5v-9h2L12 3Z',
        ],
        [
            'label' => 'Manajemen Siswa',
            'route' => 'manajemen-siswa',
            'line' => 'M16 14a4 4 0 0 1 4 4v1H4v-1a4 4 0 0 1 4-4m8 0a4 4 0 1 0-8 0',
            'solid' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 8a8 8 0 0 1 16 0H4Z',
        ],
        [
            'label' => 'Log Activity Siswa',
            'route' => 'log-activity',
            'line' => 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
            'solid' => 'M7 5h14v2H7V5Zm0 6h14v2H7v-2Zm0 6h14v2H7v-2ZM3 5h2v2H3V5Zm0 6h2v2H3v-2Zm0 6h2v2H3v-2Z',
        ],
        [
            'label' => 'Analisis Perilaku',
            'route' => 'analisis-perilaku',
            'line' => 'M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-7',
            'solid' => 'M3 4h2v14h16v2H3V4Zm4 8h3v5H7v-5Zm5-5h3v10h-3V7Zm5 3h3v7h-3v-7Z',
        ],
        [
            'label' => 'Manajemen Rekomendasi',
            'route' => 'manajemen-rekomendasi',
            'line' => 'M7.5 8.25h9m-9 3.75h5.25M21 12c0 4.556-3.86 8.25-8.625 8.25a9.28 9.28 0 0 1-3.936-.865L3 21l1.615-4.439A7.88 7.88 0 0 1 3 12c0-4.556 3.86-8.25 8.625-8.25S21 7.444 21 12Z',
            'solid' => 'M12 3C7.03 3 3 6.58 3 11c0 1.7.6 3.28 1.63 4.58L3.5 20.5l4.78-1.55A9.93 9.93 0 0 0 12 19c4.97 0 9-3.58 9-8s-4.03-8-9-8Zm-4 6h8v2H8V9Zm0 4h5v2H8v-2Z',
        ],
        [
            'label' => 'Manajemen Role',
            'route' => 'manajemen-role',
            'line' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 16.5 0',
            'solid' => 'M12 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 11a8 8 0 0 1 16 0H4Z',
        ],
    ];
@endphp

<div x-data="{ sidebarCollapsed: false }" class="flex min-h-screen flex-col md:flex-row">

    <aside
        class="sticky top-0 z-30 flex flex-col bg-gradient-to-b from-indigo-700 to-slate-950 p-4 text-white transition-all duration-300 md:h-screen md:self-start"
        x-bind:class="sidebarCollapsed ? 'md:w-20' : 'md:w-64'"
    >
        <div class="min-h-0 flex-1">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="min-w-0" x-show="!sidebarCollapsed" x-transition>
                    <h1 class="truncate text-xl font-bold">Dashboard Monitoring</h1>
                    <p class="truncate text-xs opacity-70">Deteksi Dini Cyberbullying</p>
                </div>

                <button
                    type="button"
                    x-on:click="sidebarCollapsed = ! sidebarCollapsed"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20"
                    title="Toggle sidebar"
                >
                    <svg x-show="!sidebarCollapsed" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 6 9 12l6 6"/></svg>
                    <svg x-show="sidebarCollapsed" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
            </div>

            <nav class="flex flex-wrap gap-2 md:block md:space-y-2">
                @foreach($navItems as $item)
                    @php($active = request()->routeIs($item['route']))
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex shrink-0 items-center gap-3 rounded-lg p-3 text-sm transition md:text-base {{ $active ? 'bg-white/20 text-white' : 'opacity-80 hover:bg-white/10' }}"
                        x-bind:class="sidebarCollapsed ? 'md:justify-center' : ''"
                        title="{{ $item['label'] }}"
                    >
                        @if($active)
                            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $item['solid'] }}"/></svg>
                        @else
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['line'] }}"/></svg>
                        @endif
                        <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 shrink-0">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg p-3 text-sm opacity-80 transition hover:bg-white/10 md:text-base"
                x-bind:class="sidebarCollapsed ? 'md:justify-center' : ''"
                title="Logout"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Logout</span>
            </button>
        </form>
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6">
        {{ $slot }}
    </main>

</div>

@livewireScripts
</body>
</html>
