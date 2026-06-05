<div class="min-w-0 space-y-6 overflow-x-hidden" x-data="{ exportOpen: false }">
    <div id="dashboard-report" class="min-w-0 space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-7"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="truncate text-2xl font-bold text-slate-900">Student Behavior Monitoring Dashboard</h1>
                            <p class="mt-1 text-sm text-slate-500">Behavior Analysis and Risk Monitoring System</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm hover:bg-slate-50" title="Notification">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 0 1-5.714 0"/></svg>
                        </button>
                        <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm hover:bg-slate-50" title="Home">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                        </a>
                        <div class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ $userInitials }}</div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $userName }}</p>
                                <p class="text-xs text-slate-500">{{ $userRole }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 border-t border-slate-200 pt-4 lg:flex-row lg:items-center lg:justify-between">
                    <select wire:model.live="classFilter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <option value="">All Classes</option>
                        @foreach($classOptions as $class)
                            <option value="{{ $class }}">{{ $class }}</option>
                        @endforeach
                    </select>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 9V3.75h10.5V9M6.75 18.75h10.5V15H6.75v3.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15H5.25A2.25 2.25 0 0 1 3 12.75v-3A2.25 2.25 0 0 1 5.25 7.5h13.5A2.25 2.25 0 0 1 21 9.75v3A2.25 2.25 0 0 1 18.75 15h-1.5"/></svg>
                            Cetak Laporan
                        </button>
                        <div class="relative" x-on:click.outside="exportOpen = false">
                            <button type="button" x-on:click="exportOpen = ! exportOpen" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3m0 0 4 4m-4-4-4 4M4 19h16"/></svg>
                                Unduh Data
                            </button>
                            <div x-show="exportOpen" x-transition class="absolute right-0 z-20 mt-2 w-44 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                                <button wire:click="exportPdf" x-on:click="exportOpen = false" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">PDF</button>
                                <button wire:click="exportExcel" x-on:click="exportOpen = false" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Risk Trend</h2>
            <p class="mt-1 text-sm text-slate-500">Risk status changes over behavior analysis periods.</p>
            <div class="mt-4 h-80 w-full min-w-0">
                <canvas id="dashboardRiskTrendChart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="grid min-w-0 items-stretch gap-4 md:grid-cols-3">
                <div class="h-full rounded-lg border border-emerald-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Tidak Berisiko</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $safeCount }} Siswa</p>
                        </div>
                        <div class="rounded-lg bg-emerald-100 p-3 text-emerald-700"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.25c0 4.8 3.06 9.08 7.5 10.5 4.44-1.42 7.5-5.7 7.5-10.5V6L12 3Z"/></svg></div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 font-semibold text-emerald-700">{{ $safeTrend >= 0 ? '+' : '' }}{{ $safeTrend }}</span>
                        <span class="text-slate-500">Last Update: {{ $lastUpdated }}</span>
                    </div>
                </div>
                <div class="h-full rounded-lg border border-red-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Berisiko</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $riskCount }} Siswa</p>
                        </div>
                        <div class="rounded-lg bg-red-100 p-3 text-red-700"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg></div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="rounded-full bg-red-100 px-3 py-1 font-semibold text-red-700">{{ $riskTrend >= 0 ? '+' : '' }}{{ $riskTrend }}</span>
                        <span class="text-slate-500">Last Update: {{ $lastUpdated }}</span>
                    </div>
                </div>

                <div class="flex h-full flex-col rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-indigo-100 p-3 text-indigo-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75 13.545 10l3.58.52-2.59 2.524.612 3.566L12 14.953 8.853 16.61l.611-3.566-2.589-2.524 3.58-.52L12 6.75Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5V5.75A2.75 2.75 0 0 1 6.75 3h10.5A2.75 2.75 0 0 1 20 5.75V19.5"/></svg></div>
                        <h2 class="text-base font-bold text-slate-900">Informasi Edukasi</h2>
                    </div>
                    <div class="mt-4 grid flex-1 content-start gap-3">
                        <button wire:click="openEducation('risk')" class="rounded-lg bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Siswa Berisiko</button>
                        <button wire:click="openEducation('safe')" class="rounded-lg bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">Siswa Tidak Berisiko</button>
                    </div>
                </div>
        </div>

        <div class="grid min-w-0 items-start gap-4 xl:grid-cols-2">
            <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-bold text-slate-900">Class Leaderboard</h2>
                <div class="mt-4 space-y-3">
                    @forelse($leaderboard as $index => $row)
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-500">#{{ $index + 1 }}</span>
                                <span class="text-sm font-bold {{ $row['change'] > 0 ? 'text-emerald-700' : ($row['change'] < 0 ? 'text-red-700' : 'text-slate-500') }}">{{ $row['change'] > 0 ? 'Up' : ($row['change'] < 0 ? 'Down' : 'Stable') }}</span>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-900">{{ $row['class'] }}</p>
                                <p class="text-xs text-slate-500">{{ $row['change'] >= 0 ? '+' : '' }}{{ $row['change'] }} students recovered</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No class trend data.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex max-h-[650px] min-h-[500px] min-w-0 flex-col rounded-lg border border-red-200 bg-white p-4 shadow-sm xl:h-[clamp(500px,60vh,650px)]">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Perlu Tindak Lanjut</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $followUps->count() }} siswa perlu perhatian</p>
                    </div>
                    <div class="rounded-lg bg-red-100 p-3 text-red-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg></div>
                </div>
                <div class="mt-4 min-h-0 flex-1 space-y-3 overflow-y-auto pr-1">
                    @forelse($followUps as $item)
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    @if($item->student && $this->photoUrl($item->student->profile_photo))
                                        <img src="{{ $this->photoUrl($item->student->profile_photo) }}" alt="{{ $item->student->name }}" class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-white">
                                    @else
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                                            {{ strtoupper(substr($item->student->name ?? $item->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-900">{{ $item->student->name ?? $item->name }}</p>
                                    <span class="mt-1 inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Berisiko</span>
                                    <p class="mt-2 text-xs font-semibold text-slate-700">{{ $this->highestBehaviorText($item) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ optional($item->last_update)->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <button wire:click="showDetail({{ $item->id }})" title="Detail" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No risk students in latest data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-base font-bold text-slate-900">Indikator Perilaku</h2>
        <p class="mt-1 text-sm text-slate-500">Average behavioral indicators from latest student analysis data.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach($indicatorAverages as $indicator)
                <div class="rounded-lg bg-slate-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="truncate text-sm font-semibold text-slate-700">{{ $indicator['label'] }}</p>
                        <p class="text-sm font-bold text-slate-900">{{ $indicator['percent'] }}%</p>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $indicator['percent'] }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Average Score: {{ number_format($indicator['score'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @if($showEducationModal)
        <div class="fixed inset-0 z-40 !m-0 flex items-center justify-center bg-slate-950/50 p-4 sm:p-6">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900">{{ $educationType === 'risk' ? 'Siswa Berisiko' : 'Siswa Tidak Berisiko' }}</h2>
                    <button wire:click="$set('showEducationModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="space-y-4 px-5 py-6 text-sm leading-6 text-slate-700">
                    @if($educationType === 'risk')
                        <p>Siswa diklasifikasikan berisiko ketika data analisis terbaru menunjukkan pola perilaku yang membutuhkan tindak lanjut, seperti agresi tinggi, stabilitas emosi rendah, atau efek anonimitas yang dominan.</p>
                        <p>Interpretasi ini membantu wali kelas memprioritaskan pendampingan, validasi konteks, dan komunikasi lanjutan dengan siswa.</p>
                    @else
                        <p>Siswa tidak berisiko berarti data analisis terbaru tidak menunjukkan indikator dominan yang membutuhkan tindak lanjut segera.</p>
                        <p>Status ini tetap perlu dipantau secara berkala karena perubahan perilaku dapat muncul pada periode analisis berikutnya.</p>
                    @endif
                    <div class="rounded-lg bg-slate-50 px-4 py-3">
                        <p class="font-bold text-slate-900">Panduan Indikator</p>
                        <p class="mt-2">Empathy, conformity, aggression, emotion stability, dan anonymity effect dibaca sebagai sinyal perilaku. Nilai tertinggi membantu menentukan rekomendasi tindak lanjut yang paling relevan.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.partials.behavior-analysis-modals')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', renderDashboardRiskTrend);
        document.addEventListener('DOMContentLoaded', renderDashboardRiskTrend);

        function renderDashboardRiskTrend() {
            const canvas = document.getElementById('dashboardRiskTrendChart');
            if (!canvas || !window.Chart) return;

            if (window.dashboardRiskTrendChartInstance) {
                window.dashboardRiskTrendChartInstance.destroy();
            }

            const chartData = @json($trendChart);
            window.dashboardRiskTrendChartInstance = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Berisiko', data: chartData.risk, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.12)', tension: .35, fill: true },
                        { label: 'Tidak Berisiko', data: chartData.safe, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', tension: .35, fill: true }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0, maxTicksLimit: 5 } } }
                }
            });
        }
    </script>
</div>
