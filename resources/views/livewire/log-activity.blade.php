<div
    class="space-y-6"
    x-data="{
        toast: null,
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
        class="fixed right-4 top-4 z-50 rounded-lg border border-emerald-200 bg-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-lg"
        x-text="toast?.message"
    ></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Log Activity</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Activity History</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor imported and analyzed student activity records.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Activity</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalActivities }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Fastest Response Time</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($fastestResponseTime, 2) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Longest Response Time</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($longestResponseTime, 2) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Average Response Time</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($averageResponseTime, 2) }}</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_160px]">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Search by NIS or name" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select wire:model.live="classFilter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Classes</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
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
                <div class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">Loading activity history...</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach([
                                'student_id' => 'ID',
                                'name' => 'Nama',
                                'class' => 'Kelas',
                                'response_time_mean' => 'Response Time',
                                'last_update' => 'Last Update',
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
                        @forelse($logs as $log)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $log->student_id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $log->student->name ?? $log->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $log->student->class ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ number_format($log->response_time_mean, 5) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ optional($log->last_update)->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="showDetail({{ $log->id }})" title="View" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-800">No activity history found</p>
                                        <p class="mt-1 text-sm text-slate-500">Activity data will appear after behavior analysis records are added.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($perPage !== 'all')
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    @if($showDetailModal && $selectedLogActivity)
        <div class="fixed inset-0 z-40 !m-0 flex items-center justify-center bg-slate-950/50 p-4 sm:p-6">
            <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900">Activity Detail</h2>
                    <button wire:click="$set('showDetailModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-5 px-5 py-6">
                    <div class="rounded-lg bg-slate-50 px-4 py-5 text-center">
                        @if($selectedLogActivity->student && $this->photoUrl($selectedLogActivity->student->profile_photo))
                            <img src="{{ $this->photoUrl($selectedLogActivity->student->profile_photo) }}" alt="{{ $selectedLogActivity->name }}" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-indigo-50">
                        @else
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-indigo-100 text-3xl font-bold text-indigo-700">
                                {{ strtoupper(substr($selectedLogActivity->name, 0, 1)) }}
                            </div>
                        @endif
                        <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $selectedLogActivity->student->name ?? $selectedLogActivity->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $selectedLogActivity->student_id }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">{{ $selectedLogActivity->student->class ?? '-' }}</p>
                    </div>

                    @if($selectedLatestActivity)
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Latest Behavior Score</h3>
                            <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach([
                                    'Empathy Score' => number_format($selectedLatestActivity->empathy_score, 5),
                                    'Conformity Index' => number_format($selectedLatestActivity->conformity_index, 5),
                                    'Aggression Score' => number_format($selectedLatestActivity->aggression_score, 5),
                                    'Emotion Stability' => number_format($selectedLatestActivity->emotion_stability, 5),
                                    'Anonymity Effect' => number_format($selectedLatestActivity->anonymity_effect, 5),
                                    'Final Empathy' => number_format($selectedLatestActivity->final_empathy, 5),
                                    'Risk Score' => number_format($selectedLatestActivity->risk_score, 5),
                                    'Risk Label' => $this->riskLabelText($selectedLatestActivity->risk_label),
                                ] as $label => $value)
                                    <div class="rounded-lg bg-slate-50 px-4 py-3">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Activity History</h3>
                        <div class="mt-4 grid gap-3">
                            @forelse($selectedActivityHistory as $history)
                                <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm font-semibold text-slate-900">{{ optional($history->last_update)->format('d/m/Y H:i:s') }}</p>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ (int) $history->risk_label === 1 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $this->riskLabelText($history->risk_label) }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-500">Response Time: {{ number_format($history->response_time_mean, 5) }} | Risk Score: {{ number_format($history->risk_score, 5) }}</p>
                                </div>
                            @empty
                                <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No previous activity history for this student.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
