<div
    class="min-w-0 space-y-6"
    x-data="{
        toast: null,
        exportOpen: false,
        showToast(event) {
            this.toast = event.detail;
            setTimeout(() => this.toast = null, 3000);
        }
    }"
    x-on:notify.window="showToast($event)"
>
    <div
        x-show="toast"
        x-transition
        class="fixed right-4 top-4 z-50 rounded-lg border px-4 py-3 text-sm font-medium shadow-lg"
        x-bind:class="toast?.type === 'error' ? 'border-red-200 bg-white text-red-700' : 'border-emerald-200 bg-white text-emerald-700'"
        x-text="toast?.message"
    ></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Analisis Perilaku</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Behavior Analysis Management</h1>
            <p class="mt-1 text-sm text-slate-500">Import, analyze, and manage student behavior score records.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                Add Manual
            </button>

            <form wire:submit.prevent="importExcel">
                <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0-12 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    <span>Import</span>
                    <input wire:model="importFile" type="file" accept=".csv,.txt,.xls,.xlsx" class="hidden" onchange="this.form.requestSubmit()">
                </label>
            </form>

            <div class="relative" x-on:click.outside="exportOpen = false">
                <button type="button" x-on:click="exportOpen = ! exportOpen" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 lg:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 19h16"/></svg>
                    Export
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="exportOpen" x-transition class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button wire:click="exportPdf" x-on:click="exportOpen = false" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Export to PDF</button>
                    <button wire:click="exportExcel" x-on:click="exportOpen = false" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Export to Excel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
        <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Average Behavior Score</h2>
                    <p class="mt-1 text-sm text-slate-500">Average comparison for each behavior score.</p>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700">
                    {{ $riskStudents }} Siswa Berisiko
                </div>
            </div>
            <div class="mt-4 h-64 w-full min-w-0">
                <canvas id="behaviorAverageChart" class="h-full w-full"></canvas>
            </div>
        </div>

        <div class="min-w-0 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-bold text-slate-900">Class Risk</h2>
            <p class="mt-1 text-sm text-slate-500">Latest risk status by class.</p>
            <div class="mt-4 space-y-3">
                @forelse($classRiskStats as $classRisk)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                        <p class="font-bold text-slate-900">{{ $classRisk->class }}</p>
                        <p class="text-sm font-semibold text-red-700">{{ $classRisk->total }} siswa berisiko</p>
                    </div>
                @empty
                    <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No risky students in latest data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="min-w-0 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_160px]">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Search by NIS or name" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select wire:model.live="riskFilter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Risk Labels</option>
                    <option value="0">Tidak Berisiko</option>
                    <option value="1">Berisiko</option>
                </select>

                <select wire:model.live="perPage" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>

        <div class="relative overflow-hidden">
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/70 backdrop-blur-sm">
                <div class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">Loading analysis data...</div>
            </div>

            <div class="max-w-full overflow-x-auto scroll-smooth">
                <table class="min-w-[1500px] divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach([
                                'student_id' => 'ID',
                                'name' => 'Nama',
                                'class' => 'Kelas',
                                'empathy_score' => 'empathy_score',
                                'conformity_index' => 'conformity_index',
                                'aggression_score' => 'aggression_score',
                                'emotion_stability' => 'emotion_stability',
                                'anonymity_effect' => 'anonymity_effect',
                                'final_empathy' => 'final_empathy',
                                'risk_score' => 'risk_score',
                                'risk_label' => 'risk_label',
                            ] as $field => $label)
                                <th class="px-4 py-3">
                                    <button wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 hover:text-indigo-600">
                                        {{ $label }}
                                        <span class="text-slate-400">
                                            @if($sortField === $field && $sortDirection === 'desc')
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($analyses as $analysis)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $analysis->student_id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $analysis->student->name ?? $analysis->name }}</td>
                                <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $analysis->student->class ?? '-' }}</span></td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->empathy_score, 3) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->conformity_index, 3) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->aggression_score, 3) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->emotion_stability, 3) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->anonymity_effect, 3) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->final_empathy, 3) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($analysis->risk_score, 3) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ (int) $analysis->risk_label === 1 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $this->riskLabelText($analysis->risk_label) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="showDetail({{ $analysis->id }})" title="View" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                                        </button>
                                        <button wire:click="openEditModal({{ $analysis->id }})" title="Edit" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L8.25 18.402 3.75 19.5l1.098-4.5 12.014-10.513Z"/></svg>
                                        </button>
                                        <button wire:click="delete({{ $analysis->id }})" wire:confirm="Delete this analysis record?" title="Delete" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-7"/></svg>
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-800">No behavior analysis data found</p>
                                        <p class="mt-1 text-sm text-slate-500">Import data or add a manual analysis record.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($perPage !== 'all')
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $analyses->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('livewire.partials.behavior-analysis-modals')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', renderBehaviorAverageChart);
        document.addEventListener('DOMContentLoaded', renderBehaviorAverageChart);

        function renderBehaviorAverageChart() {
            const canvas = document.getElementById('behaviorAverageChart');
            if (!canvas || !window.Chart) return;

            if (window.behaviorAverageChartInstance) {
                window.behaviorAverageChartInstance.destroy();
            }

            const averages = @json($chartAverages);
            window.behaviorAverageChartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: Object.keys(averages),
                    datasets: [{
                        data: Object.values(averages),
                        backgroundColor: ['#4f46e5', '#0f172a', '#ef4444', '#64748b', '#14b8a6', '#22c55e', '#f59e0b'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    </script>
</div>
