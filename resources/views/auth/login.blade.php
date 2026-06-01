<!DOCTYPE html>
<html>
<head>
    <title>Login - Dashboard Monitoring</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-100 text-slate-900">
    <main class="flex min-h-screen w-full items-stretch justify-center p-4 sm:p-6 lg:p-8">
        <div class="grid min-h-[calc(100vh-2rem)] w-full max-w-7xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm sm:min-h-[calc(100vh-3rem)] lg:min-h-[calc(100vh-4rem)] lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section class="flex min-w-0 items-center px-6 py-8 sm:px-10 lg:px-12">
                <div class="mx-auto w-full max-w-md">
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-7"/></svg>
                        </div>
                        <p class="mt-5 text-sm font-semibold uppercase tracking-wide text-indigo-600">Dashboard Monitoring</p>
                        <h1 class="mt-2 text-2xl font-bold text-slate-900">Welcome Back</h1>
                        <p class="mt-2 text-sm text-slate-500">Sign in to continue monitoring student activity and behavior analysis.</p>
                    </div>

                    @if(session('status'))
                        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="username" class="text-sm font-semibold text-slate-700">Username</label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="{{ old('username') }}"
                                autocomplete="username"
                                autofocus
                                class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                            >
                            @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                            >
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                            Login
                        </button>
                    </form>
                </div>
            </section>

            <section class="hidden min-w-0 bg-gradient-to-b from-indigo-700 to-slate-950 p-6 text-white lg:flex lg:items-center">
                <div class="w-full min-w-0">
                    <div class="rounded-lg border border-white/10 bg-white/10 p-5 shadow-sm backdrop-blur">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-white/70">Live Overview</p>
                                <h2 class="mt-1 text-xl font-bold">Student Safety Monitor</h2>
                            </div>
                            <div class="rounded-lg bg-white/10 p-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3"/></svg>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg bg-white p-4 text-slate-900">
                                <p class="text-xs text-slate-500">Activity</p>
                                <p class="mt-2 text-2xl font-bold">128</p>
                            </div>
                            <div class="rounded-lg bg-white p-4 text-slate-900">
                                <p class="text-xs text-slate-500">Students</p>
                                <p class="mt-2 text-2xl font-bold">42</p>
                            </div>
                            <div class="rounded-lg bg-red-50 p-4 text-red-700">
                                <p class="text-xs">Risk</p>
                                <p class="mt-2 text-2xl font-bold">7</p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-lg bg-white p-4 text-slate-900">
                            <div class="flex items-end gap-2">
                                <div class="h-20 flex-1 rounded bg-indigo-100"></div>
                                <div class="h-28 flex-1 rounded bg-slate-200"></div>
                                <div class="h-16 flex-1 rounded bg-red-100"></div>
                                <div class="h-32 flex-1 rounded bg-indigo-600"></div>
                                <div class="h-24 flex-1 rounded bg-emerald-100"></div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-6 text-sm leading-6 text-white/70">A focused workspace for tracking student management, activity history, and behavior analysis in one protected dashboard.</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
