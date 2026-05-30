@if($showFormModal)
    <div class="fixed inset-0 z-40 !m-0 flex items-center justify-center bg-slate-950/50 p-4 sm:p-6">
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">{{ $editId ? 'Edit Analysis' : 'Add Analysis' }}</h2>
                <button wire:click="$set('showFormModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="space-y-4 px-5 py-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Student ID / NIS</label>
                        <input wire:model="student_id" type="text" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        @error('student_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Name</label>
                        <input wire:model="name" type="text" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach([
                        'response_time_mean' => 'Response Time Mean',
                        'empathy_score' => 'Empathy Score',
                        'conformity_index' => 'Conformity Index',
                        'aggression_score' => 'Aggression Score',
                        'emotion_stability' => 'Emotion Stability',
                        'anonymity_effect' => 'Anonymity Effect',
                        'final_empathy' => 'Final Empathy',
                        'risk_score' => 'Risk Score',
                    ] as $field => $label)
                        <div>
                            <label class="text-sm font-semibold text-slate-700">{{ $label }}</label>
                            <input wire:model="{{ $field }}" type="number" step="0.00001" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Risk Label</label>
                        <select wire:model="risk_label" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            <option value="0">Tidak Berisiko</option>
                            <option value="1">Berisiko</option>
                        </select>
                        @error('risk_label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Last Update</label>
                        <input wire:model="last_update" type="datetime-local" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        @error('last_update') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showFormModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save Analysis</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($showDetailModal && $selectedAnalysis)
    <div class="fixed inset-0 z-40 !m-0 flex items-center justify-center bg-slate-950/50 p-4 sm:p-6">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-lg bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold text-slate-900">Analysis Detail</h2>
                <button wire:click="$set('showDetailModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-5 px-5 py-6">
                <div class="rounded-lg bg-slate-50 px-4 py-5 text-center">
                    @if($selectedAnalysis->student && $this->photoUrl($selectedAnalysis->student->profile_photo))
                        <img src="{{ $this->photoUrl($selectedAnalysis->student->profile_photo) }}" alt="{{ $selectedAnalysis->name }}" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-indigo-50">
                    @else
                        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-indigo-100 text-3xl font-bold text-indigo-700">
                            {{ strtoupper(substr($selectedAnalysis->name, 0, 1)) }}
                        </div>
                    @endif
                    <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $selectedAnalysis->student->name ?? $selectedAnalysis->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $selectedAnalysis->student_id }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ $selectedAnalysis->student->class ?? '-' }}</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ (int) $selectedAnalysis->risk_label === 1 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $this->riskLabelText($selectedAnalysis->risk_label) }}</span>
                        @if($selectedIsLatest)
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">Latest Data</span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Current Viewed Score</h3>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach([
                            'Empathy Score' => number_format($selectedAnalysis->empathy_score, 5),
                            'Conformity Index' => number_format($selectedAnalysis->conformity_index, 5),
                            'Aggression Score' => number_format($selectedAnalysis->aggression_score, 5),
                            'Emotion Stability' => number_format($selectedAnalysis->emotion_stability, 5),
                            'Anonymity Effect' => number_format($selectedAnalysis->anonymity_effect, 5),
                            'Final Empathy' => number_format($selectedAnalysis->final_empathy, 5),
                            'Risk Score' => number_format($selectedAnalysis->risk_score, 5),
                            'Risk Label' => $this->riskLabelText($selectedAnalysis->risk_label),
                        ] as $label => $value)
                            <div class="rounded-lg bg-slate-50 px-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">History Score</h3>
                    <div class="mt-4 grid gap-3">
                        @forelse($selectedHistory as $history)
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-semibold text-slate-900">{{ optional($history->last_update)->format('d/m/Y H:i:s') }}</p>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ (int) $history->risk_label === 1 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $this->riskLabelText($history->risk_label) }}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Empathy: {{ number_format($history->empathy_score, 5) }} | Aggression: {{ number_format($history->aggression_score, 5) }} | Risk Score: {{ number_format($history->risk_score, 5) }}</p>
                            </div>
                        @empty
                            <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No previous behavior score for this student.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
