<?php

namespace App\Livewire;

use App\Models\LogActivity as LogActivityModel;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use ZipArchive;

class LogActivity extends Component
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
    public ?LogActivityModel $selectedLogActivity = null;

    public $importFile;

    public string $student_id = '';
    public string $name = '';
    public string $response_time_mean = '';
    public string $empathy_score = '';
    public string $conformity_index = '';
    public string $aggression_score = '';
    public string $emotion_stability = '';
    public string $anonymity_effect = '';
    public string $final_empathy = '';
    public string $risk_score = '';
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
        if (! in_array($field, ['id', 'name', 'response_time_mean', 'risk_label', 'last_update'], true)) {
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
        $log = LogActivityModel::findOrFail($id);

        $this->editId = $log->id;
        $this->student_id = $log->student_id;
        $this->name = $log->name;
        $this->response_time_mean = (string) $log->response_time_mean;
        $this->empathy_score = (string) $log->empathy_score;
        $this->conformity_index = (string) $log->conformity_index;
        $this->aggression_score = (string) $log->aggression_score;
        $this->emotion_stability = (string) $log->emotion_stability;
        $this->anonymity_effect = (string) $log->anonymity_effect;
        $this->final_empathy = (string) $log->final_empathy;
        $this->risk_score = (string) $log->risk_score;
        $this->risk_label = (string) $log->risk_label;
        $this->last_update = optional($log->last_update)->format('Y-m-d\TH:i') ?? '';
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function showDetail(int $id): void
    {
        $this->selectedLogActivity = LogActivityModel::with('student')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), [
            'student_id.exists' => 'Student ID / NIS must exist in Student Management.',
            'risk_label.in' => 'Risk Label must be 0 or 1.',
        ]);

        $student = Student::where('nis', $validated['student_id'])->firstOrFail();
        $validated['name'] = trim($validated['name'] ?: $student->name);
        $validated['last_update'] = $this->parseDateTime($validated['last_update']) ?? now();

        LogActivityModel::updateOrCreate(['id' => $this->editId], $validated);

        $this->showFormModal = false;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Log activity saved successfully.');
    }

    public function delete(int $id): void
    {
        LogActivityModel::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Log activity deleted successfully.');
    }

    public function importExcel(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:10240'],
        ]);

        $rows = $this->readImportRows($this->importFile->getRealPath(), $this->importFile->getClientOriginalExtension());
        $imported = 0;
        $skipped = 0;
        $missingStudent = 0;
        $invalidRow = 0;

        foreach (array_chunk($rows, 250) as $chunk) {
            foreach ($chunk as $row) {
                if ($this->isHeaderRow($row)) {
                    continue;
                }

                $mapped = $this->mapImportRow($row);

                if (! $mapped) {
                    $skipped++;
                    $invalidRow++;
                    continue;
                }

                $student = Student::where('nis', $mapped['student_id'])->first();

                if (! $student) {
                    $skipped++;
                    $missingStudent++;
                    continue;
                }

                LogActivityModel::create($mapped);
                $imported++;
            }
        }

        $this->importFile = null;
        $this->resetPage();

        $message = "Import completed. {$imported} imported, {$skipped} skipped.";
        if ($missingStudent > 0) {
            $message .= " {$missingStudent} skipped because NIS was not found.";
        }
        if ($invalidRow > 0) {
            $message .= " {$invalidRow} skipped because row data was invalid.";
        }

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function exportExcel()
    {
        $logs = $this->filteredQuery()->orderBy($this->sortField, $this->sortDirection)->get();
        $filename = 'log-activities-' . now()->format('Y-m-d-His') . '.xls';

        return response()->streamDownload(function () use ($logs) {
            echo '<table border="1">';
            echo '<tr><th>Student ID / NIS</th><th>Name</th><th>Response Time Mean</th><th>Empathy Score</th><th>Conformity Index</th><th>Aggression Score</th><th>Emotion Stability</th><th>Anonymity Effect</th><th>Final Empathy</th><th>Risk Score</th><th>Risk Label</th><th>Last Update</th></tr>';

            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . e($log->student_id) . '</td>';
                echo '<td>' . e($log->name) . '</td>';
                echo '<td>' . e($log->response_time_mean) . '</td>';
                echo '<td>' . e($log->empathy_score) . '</td>';
                echo '<td>' . e($log->conformity_index) . '</td>';
                echo '<td>' . e($log->aggression_score) . '</td>';
                echo '<td>' . e($log->emotion_stability) . '</td>';
                echo '<td>' . e($log->anonymity_effect) . '</td>';
                echo '<td>' . e($log->final_empathy) . '</td>';
                echo '<td>' . e($log->risk_score) . '</td>';
                echo '<td>' . e($this->riskLabelText($log->risk_label)) . '</td>';
                echo '<td>' . e(optional($log->last_update)->format('d/m/Y H:i:s')) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function exportPdf()
    {
        $logs = $this->filteredQuery()->orderBy($this->sortField, $this->sortDirection)->get();
        $filename = 'log-activities-' . now()->format('Y-m-d-His') . '.pdf';

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
            'response_time_mean',
            'empathy_score',
            'conformity_index',
            'aggression_score',
            'emotion_stability',
            'anonymity_effect',
            'final_empathy',
            'risk_score',
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

    public function getTotalActivitiesProperty(): int
    {
        return LogActivityModel::count();
    }

    public function getRiskStudentsProperty(): int
    {
        return LogActivityModel::where('risk_label', 1)->distinct('student_id')->count('student_id');
    }

    public function getAverageResponseTimeProperty(): float
    {
        return (float) LogActivityModel::avg('response_time_mean');
    }

    public function render()
    {
        $query = $this->filteredQuery()->orderBy($this->sortField, $this->sortDirection);
        $logs = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $this->perPage);

        return view('livewire.log-activity', [
            'logs' => $logs,
            'totalActivities' => $this->totalActivities,
            'riskStudents' => $this->riskStudents,
            'averageResponseTime' => $this->averageResponseTime,
        ])->layout('layouts.app');
    }

    private function rules(): array
    {
        return [
            'student_id' => ['required', 'string', 'max:50', Rule::exists('students', 'nis')],
            'name' => ['required', 'string', 'max:150'],
            'response_time_mean' => ['required', 'numeric'],
            'empathy_score' => ['required', 'numeric'],
            'conformity_index' => ['required', 'numeric'],
            'aggression_score' => ['required', 'numeric'],
            'emotion_stability' => ['required', 'numeric'],
            'anonymity_effect' => ['required', 'numeric'],
            'final_empathy' => ['required', 'numeric'],
            'risk_score' => ['required', 'numeric'],
            'risk_label' => ['required', Rule::in(['0', '1', 0, 1])],
            'last_update' => ['required', 'date'],
        ];
    }

    private function filteredQuery()
    {
        return LogActivityModel::query()
            ->with('student')
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('student_id', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', $search));
                });
            })
            ->when($this->riskFilter !== '', fn ($query) => $query->where('risk_label', (int) $this->riskFilter));
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
        $lines = ['Log Activity Report', 'Generated: ' . now()->format('Y-m-d H:i'), ''];
        $lines[] = 'NIS | Name | Response | Risk | Last Update';

        foreach ($logs as $log) {
            $lines[] = "{$log->student_id} | {$log->name} | {$log->response_time_mean} | {$this->riskLabelText($log->risk_label)} | " . optional($log->last_update)->format('d/m/Y H:i:s');
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
