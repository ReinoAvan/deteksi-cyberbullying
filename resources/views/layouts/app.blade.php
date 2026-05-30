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
    ];
@endphp

<div x-data="{ sidebarCollapsed: false }" class="flex min-h-screen flex-col md:flex-row">

    <aside
        class="sticky top-0 z-30 bg-gradient-to-b from-indigo-700 to-slate-950 p-4 text-white transition-all duration-300 md:h-screen md:self-start"
        x-bind:class="sidebarCollapsed ? 'md:w-20' : 'md:w-64'"
    >
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

        <nav class="flex gap-2 overflow-x-auto md:block md:space-y-2">
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
    </aside>

    <main class="min-w-0 flex-1 p-4 sm:p-6">
        {{ $slot }}
    </main>

</div>

@livewireScripts
</body>
</html>
