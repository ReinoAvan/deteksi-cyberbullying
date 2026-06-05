<?php

namespace App\Livewire;

use App\Models\LogActivity;
use App\Models\Recommendation;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use ZipArchive;

class BehaviorAnalysis extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $riskFilter = '';
    public string $sortField = 'last_update';
    public string $sortDirection = 'desc';
    public string $perPage = '10';

    public bool $showFormModal = false;
    public bool $showDetailModal = false;
    public ?int $editId = null;
    public ?LogActivity $selectedAnalysis = null;
    public ?string $selectedRiskClass = null;

    public $importFile;

    public string $student_id = '';
    public string $name = '';
    public string $studentSearch = '';
    public string $response_time_mean = '';
    public string $empathy_score = '';
    public string $conformity_index = '';
    public string $aggression_score = '';
    public string $emotion_stability = '';
    public string $anonymity_effect = '';
    public string $risk_label = '0';
    public string $last_update = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'riskFilter' => ['except' => ''],
        'sortField' => ['except' => 'last_update'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => '10'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRiskFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['student_id', 'name', 'class', 'empathy_score', 'conformity_index', 'aggression_score', 'emotion_stability', 'anonymity_effect', 'risk_label', 'last_update'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->last_update = now()->format('Y-m-d\TH:i');
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $analysis = LogActivity::with('student')->findOrFail($id);

        $this->editId = $analysis->id;
        $this->student_id = $analysis->student_id;
        $this->name = $analysis->student->name ?? $analysis->name;
        $this->studentSearch = $this->name;
        $this->response_time_mean = (string) $analysis->response_time_mean;
        $this->empathy_score = (string) $analysis->empathy_score;
        $this->conformity_index = (string) $analysis->conformity_index;
        $this->aggression_score = (string) $analysis->aggression_score;
        $this->emotion_stability = (string) $analysis->emotion_stability;
        $this->anonymity_effect = (string) $analysis->anonymity_effect;
        $this->risk_label = (string) $analysis->risk_label;
        $this->last_update = optional($analysis->last_update)->format('Y-m-d\TH:i') ?? '';
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function updatedStudentSearch(): void
    {
        $this->student_id = '';
        $this->name = '';
    }

    public function selectStudent(string $nis): void
    {
        $student = Student::where('nis', $nis)->firstOrFail();

        $this->student_id = $student->nis;
        $this->name = $student->name;
        $this->studentSearch = $student->name;
        $this->resetValidation(['student_id', 'name', 'studentSearch']);
    }

    public function showDetail(int $id): void
    {
        $this->selectedAnalysis = LogActivity::with('student')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function viewClassRisk(string $className): void
    {
        $this->selectedRiskClass = $className;
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), [
            'student_id.exists' => 'Student ID / NIS must exist in Student Management.',
        ]);

        $student = Student::where('nis', $validated['student_id'])->firstOrFail();
        $validated['name'] = $student->name;
        $validated['last_update'] = $this->parseDateTime($validated['last_update']) ?? now();
        $validated['final_empathy'] = 0;
        $validated['risk_score'] = 0;
        unset($validated['studentSearch']);

        $duplicate = LogActivity::where('student_id', $validated['student_id'])
            ->where('last_update', $validated['last_update'])
            ->when($this->editId, fn ($query) => $query->where('id', '!=', $this->editId))
            ->exists();

        if ($duplicate) {
            $this->dispatch('notify', type: 'error', message: 'Duplicate data skipped: same ID and Last Update already exists.');
            return;
        }

        LogActivity::updateOrCreate(['id' => $this->editId], $validated);

        $this->showFormModal = false;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Analysis data saved successfully.');
    }

    public function delete(int $id): void
    {
        LogActivity::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Analysis data deleted successfully.');
    }

    public function importExcel(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:10240'],
        ]);

        $rows = $this->readImportRows($this->importFile->getRealPath(), $this->importFile->getClientOriginalExtension());
        $imported = 0;
        $skipped = 0;
        $duplicates = 0;
        $invalidStudents = 0;
        $invalidRows = 0;

        foreach (array_chunk($rows, 250) as $chunk) {
            foreach ($chunk as $row) {
                if ($this->isHeaderRow($row)) {
                    continue;
                }

                $mapped = $this->mapImportRow($row);

                if (! $mapped) {
                    $skipped++;
                    $invalidRows++;
                    continue;
                }

                if (! Student::where('nis', $mapped['student_id'])->exists()) {
                    $skipped++;
                    $invalidStudents++;
                    continue;
                }

                if (LogActivity::where('student_id', $mapped['student_id'])->where('last_update', $mapped['last_update'])->exists()) {
                    $skipped++;
                    $duplicates++;
                    continue;
                }

                try {
                    LogActivity::create($mapped);
                    $imported++;
                } catch (QueryException) {
                    $skipped++;
                    $duplicates++;
                }
            }
        }

        $this->importFile = null;
        $this->resetPage();

        $this->dispatch('notify', type: 'success', message: "Import completed. {$imported} imported, {$skipped} skipped, {$duplicates} duplicate, {$invalidStudents} invalid student, {$invalidRows} invalid row.");
    }

    public function exportExcel()
    {
        $logs = $this->filteredQuery()->get();
        $filename = 'behavior-analysis-' . now()->format('Y-m-d-His') . '.xls';

        return response()->streamDownload(function () use ($logs) {
            echo '<table border="1">';
            echo '<tr><th>ID</th><th>Name</th><th>Class</th><th>Empathy Score</th><th>Conformity Index</th><th>Aggression Score</th><th>Emotion Stability</th><th>Anonymity Effect</th><th>Risk Label</th><th>Last Update</th></tr>';

            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . e($log->student_id) . '</td>';
                echo '<td>' . e($log->student->name ?? $log->name) . '</td>';
                echo '<td>' . e($log->student->class ?? '-') . '</td>';
                echo '<td>' . e($log->empathy_score) . '</td>';
                echo '<td>' . e($log->conformity_index) . '</td>';
                echo '<td>' . e($log->aggression_score) . '</td>';
                echo '<td>' . e($log->emotion_stability) . '</td>';
                echo '<td>' . e($log->anonymity_effect) . '</td>';
                echo '<td>' . e($this->riskLabelText($log->risk_label)) . '</td>';
                echo '<td>' . e(optional($log->last_update)->format('d/m/Y H:i:s')) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function exportPdf()
    {
        $logs = $this->filteredQuery()->get();
        $filename = 'behavior-analysis-' . now()->format('Y-m-d-His') . '.pdf';

        return response()->streamDownload(function () use ($logs) {
            echo $this->buildSimplePdf($logs);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function resetForm(): void
    {
        $this->reset([
            'editId',
            'student_id',
            'name',
            'studentSearch',
            'response_time_mean',
            'empathy_score',
            'conformity_index',
            'aggression_score',
            'emotion_stability',
            'anonymity_effect',
            'last_update',
        ]);
        $this->risk_label = '0';
        $this->resetValidation();
    }

    public function riskLabelText(int|string|null $value): string
    {
        return (int) $value === 1 ? 'Berisiko' : 'Tidak Berisiko';
    }

    public function photoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')
            ? $path
            : asset($path);
    }

    public function behaviorLabels(): array
    {
        return [
            'empathy' => 'Empathy Score',
            'conformity' => 'Conformity Index',
            'aggression' => 'Aggression Score',
            'emotion_stability' => 'Emotion Stability',
            'anonymity_effect' => 'Anonymity Effect',
        ];
    }

    public function highestBehaviorKeys(LogActivity $analysis): array
    {
        $scores = [
            'empathy' => (float) $analysis->empathy_score,
            'conformity' => (float) $analysis->conformity_index,
            'aggression' => (float) $analysis->aggression_score,
            'emotion_stability' => (float) $analysis->emotion_stability,
            'anonymity_effect' => (float) $analysis->anonymity_effect,
        ];

        $max = max($scores);

        return array_keys(array_filter($scores, fn ($value) => $value === $max));
    }

    public function highestBehaviorText(LogActivity $analysis): string
    {
        $labels = $this->behaviorLabels();

        return collect($this->highestBehaviorKeys($analysis))
            ->map(fn ($key) => $labels[$key] ?? $key)
            ->implode(', ');
    }

    public function recommendationsFor(?LogActivity $analysis)
    {
        if (! $analysis || (int) $analysis->risk_label !== 1) {
            return collect();
        }

        return Recommendation::whereIn('nama_sikap', $this->highestBehaviorKeys($analysis))
            ->orderBy('nama_sikap')
            ->get();
    }

    public function getRiskStudentsProperty(): int
    {
        return $this->latestRowsQuery()
            ->where('latest_logs.risk_label', 1)
            ->distinct('latest_logs.student_id')
            ->count('latest_logs.student_id');
    }

    public function getChartAveragesProperty(): array
    {
        return [
            'empathy_score' => round((float) LogActivity::avg('empathy_score'), 3),
            'conformity_index' => round((float) LogActivity::avg('conformity_index'), 3),
            'aggression_score' => round((float) LogActivity::avg('aggression_score'), 3),
            'emotion_stability' => round((float) LogActivity::avg('emotion_stability'), 3),
            'anonymity_effect' => round((float) LogActivity::avg('anonymity_effect'), 3),
        ];
    }

    public function getClassRiskStatsProperty()
    {
        return $this->latestRowsQuery()
            ->join('students', 'latest_logs.student_id', '=', 'students.nis')
            ->where('latest_logs.risk_label', 1)
            ->select('students.class', DB::raw('count(distinct latest_logs.student_id) as total'))
            ->groupBy('students.class')
            ->orderBy('students.class')
            ->get();
    }

    public function getClassRiskStudentsProperty()
    {
        if (! $this->selectedRiskClass) {
            return collect();
        }

        $latestSubquery = LogActivity::query()
            ->select('student_id', DB::raw('max(last_update) as newest_update'))
            ->groupBy('student_id');

        return LogActivity::query()
            ->with('student')
            ->joinSub($latestSubquery, 'latest', function ($join) {
                $join->on('log_activities.student_id', '=', 'latest.student_id')
                    ->on('log_activities.last_update', '=', 'latest.newest_update');
            })
            ->join('students', 'log_activities.student_id', '=', 'students.nis')
            ->where('students.class', $this->selectedRiskClass)
            ->where('log_activities.risk_label', 1)
            ->select('log_activities.*')
            ->orderBy('students.name')
            ->get();
    }

    public function getSelectedIsLatestProperty(): bool
    {
        if (! $this->selectedAnalysis) {
            return false;
        }

        $latestId = LogActivity::where('student_id', $this->selectedAnalysis->student_id)
            ->orderByDesc('last_update')
            ->value('id');

        return (int) $latestId === (int) $this->selectedAnalysis->id;
    }

    public function getSelectedHistoryProperty()
    {
        if (! $this->selectedAnalysis) {
            return collect();
        }

        return LogActivity::where('student_id', $this->selectedAnalysis->student_id)
            ->where('id', '!=', $this->selectedAnalysis->id)
            ->orderByDesc('last_update')
            ->get();
    }

    public function getStudentSuggestionsProperty()
    {
        if (! $this->showFormModal || strlen(trim($this->studentSearch)) < 2 || $this->student_id !== '') {
            return collect();
        }

        $search = '%' . trim($this->studentSearch) . '%';

        return Student::query()
            ->where('name', 'like', $search)
            ->orWhere('nis', 'like', $search)
            ->orderBy('name')
            ->limit(8)
            ->get(['nis', 'name', 'class', 'profile_photo']);
    }

    public function getSelectedStudentProperty(): ?Student
    {
        if ($this->student_id === '') {
            return null;
        }

        return Student::where('nis', $this->student_id)->first();
    }

    public function getSelectedRecommendationsProperty()
    {
        return $this->recommendationsFor($this->selectedAnalysis);
    }

    public function render()
    {
        $query = $this->filteredQuery();
        $analyses = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $this->perPage);

        return view('livewire.behavior-analysis', [
            'analyses' => $analyses,
            'riskStudents' => $this->riskStudents,
            'chartAverages' => $this->chartAverages,
            'classRiskStats' => $this->classRiskStats,
            'classRiskStudents' => $this->classRiskStudents,
            'selectedIsLatest' => $this->selectedIsLatest,
            'selectedHistory' => $this->selectedHistory,
            'selectedRecommendations' => $this->selectedRecommendations,
            'studentSuggestions' => $this->studentSuggestions,
            'selectedStudent' => $this->selectedStudent,
        ])->layout('layouts.app');
    }

    private function rules(): array
    {
        return [
            'student_id' => ['required', 'string', 'max:50', Rule::exists('students', 'nis')],
            'name' => ['required', 'string', 'max:150'],
            'studentSearch' => ['required', 'string', 'max:150'],
            'response_time_mean' => ['required', 'numeric'],
            'empathy_score' => ['required', 'numeric'],
            'conformity_index' => ['required', 'numeric'],
            'aggression_score' => ['required', 'numeric'],
            'emotion_stability' => ['required', 'numeric'],
            'anonymity_effect' => ['required', 'numeric'],
            'risk_label' => ['required', Rule::in(['0', '1', 0, 1])],
            'last_update' => ['required', 'date'],
        ];
    }

    private function filteredQuery()
    {
        $query = LogActivity::query()
            ->with('student')
            ->leftJoin('students', 'log_activities.student_id', '=', 'students.nis')
            ->select('log_activities.*')
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('log_activities.student_id', 'like', $search)
                        ->orWhere('log_activities.name', 'like', $search)
                        ->orWhere('students.name', 'like', $search)
                        ->orWhere('students.class', 'like', $search);
                });
            })
            ->when($this->riskFilter !== '', fn ($query) => $query->where('log_activities.risk_label', (int) $this->riskFilter));

        if ($this->sortField === 'class') {
            return $query->orderBy('students.class', $this->sortDirection);
        }

        return $query->orderBy('log_activities.' . $this->sortField, $this->sortDirection);
    }

    private function latestRowsQuery()
    {
        $latestSubquery = LogActivity::query()
            ->select('student_id', DB::raw('max(last_update) as newest_update'))
            ->groupBy('student_id');

        return DB::table('log_activities as latest_logs')
            ->joinSub($latestSubquery, 'latest', function ($join) {
                $join->on('latest_logs.student_id', '=', 'latest.student_id')
                    ->on('latest_logs.last_update', '=', 'latest.newest_update');
            });
    }

    private function mapImportRow(array $row): ?array
    {
        $row = array_values(array_pad($row, 12, null));
        $studentId = trim((string) $row[0]);

        if ($studentId === '' || trim((string) $row[1]) === '') {
            return null;
        }

        $payload = [
            'student_id' => $studentId,
            'name' => trim((string) $row[1]),
            'response_time_mean' => $this->numericValue($row[2]),
            'empathy_score' => $this->numericValue($row[3]),
            'conformity_index' => $this->numericValue($row[4]),
            'aggression_score' => $this->numericValue($row[5]),
            'emotion_stability' => $this->numericValue($row[6]),
            'anonymity_effect' => $this->numericValue($row[7]),
            'final_empathy' => $this->numericValue($row[8]),
            'risk_score' => $this->numericValue($row[9]),
            'risk_label' => (int) $this->numericValue($row[10]),
            'last_update' => $this->parseDateTime((string) $row[11]),
        ];

        if (! in_array($payload['risk_label'], [0, 1], true) || ! $payload['last_update']) {
            return null;
        }

        return $payload;
    }

    private function numericValue($value): float
    {
        return (float) str_replace(',', '.', trim((string) $value));
    }

    private function parseDateTime(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'd/m/Y H.i.s', 'd/m/Y H:i:s', 'd-m-Y H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value));
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isHeaderRow(array $row): bool
    {
        $firstColumn = strtolower(trim((string) ($row[0] ?? '')));
        return str_contains($firstColumn, 'student') || str_contains($firstColumn, 'nis');
    }

    private function readImportRows(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'xlsx') {
            return $this->readXlsxRows($path);
        }

        return array_map('str_getcsv', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    private function readXlsxRows(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string) ($si->t ?? $si->r->t ?? '');
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $type = (string) $cell['t'];
                $value = (string) $cell->v;
                $values[] = $type === 's' ? ($sharedStrings[(int) $value] ?? '') : $value;
            }
            $rows[] = $values;
        }

        return $rows;
    }

    private function buildSimplePdf($logs): string
    {
        $lines = ['Behavior Analysis Report', 'Generated: ' . now()->format('Y-m-d H:i'), ''];
        $lines[] = 'ID | Name | Class | Risk Label | Last Update';

        foreach ($logs as $log) {
            $lines[] = "{$log->student_id} | " . ($log->student->name ?? $log->name) . ' | ' . ($log->student->class ?? '-') . " | {$this->riskLabelText($log->risk_label)} | " . optional($log->last_update)->format('d/m/Y H:i:s');
        }

        $content = "BT /F1 10 Tf 36 800 Td 13 TL";
        foreach ($lines as $line) {
            $content .= ' (' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], str($line)->limit(100, '')) . ') Tj T*';
        }
        $content .= ' ET';

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length ' . strlen($content) . " >> stream\n{$content}\nendstream endobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
