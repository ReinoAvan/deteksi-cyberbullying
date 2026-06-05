<?php

namespace App\Livewire;

use App\Models\LogActivity;
use App\Models\Recommendation;
use App\Models\RoleUser;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CyberbullyingDashboard extends Component
{
    public string $classFilter = '';
    public bool $showEducationModal = false;
    public string $educationType = 'risk';
    public bool $showDetailModal = false;
    public bool $showFormModal = false;
    public ?LogActivity $selectedAnalysis = null;

    public function updatedClassFilter(): void
    {
        $this->reset(['showEducationModal', 'showDetailModal', 'selectedAnalysis']);
    }

    public function showDetail(int $id): void
    {
        $this->selectedAnalysis = LogActivity::with('student')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function openEducation(string $type): void
    {
        $this->educationType = $type;
        $this->showEducationModal = true;
    }

    public function exportExcel()
    {
        $logs = $this->filteredLogsQuery()->get();

        return response()->streamDownload(function () use ($logs) {
            echo '<table border="1"><tr><th>ID</th><th>Name</th><th>Class</th><th>Response Time</th><th>Empathy Score</th><th>Conformity Index</th><th>Aggression Score</th><th>Emotion Stability</th><th>Anonymity Effect</th><th>Risk Label</th><th>Last Update</th></tr>';
            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . e($log->student_id) . '</td>';
                echo '<td>' . e($log->student->name ?? $log->name) . '</td>';
                echo '<td>' . e($log->student->class ?? '-') . '</td>';
                echo '<td>' . e($log->response_time_mean) . '</td>';
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
        }, 'dashboard-behavior-' . now()->format('Y-m-d-His') . '.xls', ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function exportPdf()
    {
        $logs = $this->filteredLogsQuery()->get();

        return response()->streamDownload(function () use ($logs) {
            echo $this->buildSimplePdf($logs);
        }, 'dashboard-behavior-' . now()->format('Y-m-d-His') . '.pdf', ['Content-Type' => 'application/pdf']);
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

    public function getSelectedIsLatestProperty(): bool
    {
        if (! $this->selectedAnalysis) {
            return false;
        }

        return (int) LogActivity::where('student_id', $this->selectedAnalysis->student_id)
            ->orderByDesc('last_update')
            ->value('id') === (int) $this->selectedAnalysis->id;
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

    public function getSelectedRecommendationsProperty()
    {
        return $this->recommendationsFor($this->selectedAnalysis);
    }

    public function getSelectedStudentProperty(): ?Student
    {
        return null;
    }

    public function getStudentSuggestionsProperty()
    {
        return collect();
    }

    public function render()
    {
        $latestRecords = $this->latestRecords();
        $previousRecords = $this->previousRecords();
        $riskCount = $latestRecords->where('risk_label', 1)->count();
        $safeCount = $latestRecords->where('risk_label', 0)->count();
        $previousRiskCount = $previousRecords->where('risk_label', 1)->count();
        $previousSafeCount = $previousRecords->where('risk_label', 0)->count();
        $lastUpdated = optional($latestRecords->max('last_update'))->format('d M Y') ?? '-';
        $user = Auth::user();
        $role = RoleUser::where('username', $user?->username)->value('role') ?? 'Admin';

        return view('livewire.cyberbullying-dashboard', [
            'classOptions' => Student::query()->select('class')->whereNotNull('class')->distinct()->orderBy('class')->pluck('class'),
            'userName' => $user?->username ?? 'User',
            'userRole' => $role,
            'userInitials' => $this->initials($user?->username ?? 'User'),
            'riskCount' => $riskCount,
            'safeCount' => $safeCount,
            'riskTrend' => $riskCount - $previousRiskCount,
            'safeTrend' => $safeCount - $previousSafeCount,
            'lastUpdated' => $lastUpdated,
            'trendChart' => $this->riskTrendChart(),
            'leaderboard' => $this->classLeaderboard(),
            'followUps' => $latestRecords->where('risk_label', 1)->sortByDesc('last_update')->take(6)->values(),
            'indicatorAverages' => $this->indicatorAverages($latestRecords),
            'selectedIsLatest' => $this->selectedIsLatest,
            'selectedHistory' => $this->selectedHistory,
            'selectedRecommendations' => $this->selectedRecommendations,
            'studentSuggestions' => $this->studentSuggestions,
            'selectedStudent' => $this->selectedStudent,
        ])->layout('layouts.app');
    }

    private function filteredLogsQuery()
    {
        return LogActivity::query()
            ->with('student')
            ->leftJoin('students', 'log_activities.student_id', '=', 'students.nis')
            ->select('log_activities.*')
            ->when($this->classFilter !== '', fn ($query) => $query->where('students.class', $this->classFilter))
            ->orderByDesc('log_activities.last_update');
    }

    private function latestRecords()
    {
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
            ->when($this->classFilter !== '', fn ($query) => $query->where('students.class', $this->classFilter))
            ->select('log_activities.*')
            ->get();
    }

    private function previousRecords()
    {
        $latestDate = LogActivity::max('last_update');
        if (! $latestDate) {
            return collect();
        }

        $previousDate = LogActivity::where('last_update', '<', $latestDate)->max('last_update');
        if (! $previousDate) {
            return collect();
        }

        return LogActivity::query()
            ->with('student')
            ->join('students', 'log_activities.student_id', '=', 'students.nis')
            ->where('log_activities.last_update', $previousDate)
            ->when($this->classFilter !== '', fn ($query) => $query->where('students.class', $this->classFilter))
            ->select('log_activities.*')
            ->get();
    }

    private function riskTrendChart(): array
    {
        $rows = LogActivity::query()
            ->join('students', 'log_activities.student_id', '=', 'students.nis')
            ->when($this->classFilter !== '', fn ($query) => $query->where('students.class', $this->classFilter))
            ->selectRaw('date(log_activities.last_update) as period, log_activities.risk_label, count(distinct log_activities.student_id) as total')
            ->groupBy('period', 'log_activities.risk_label')
            ->orderBy('period')
            ->get();

        $periods = $rows->pluck('period')->unique()->values();

        return [
            'labels' => $periods->map(fn ($period) => date('d M', strtotime($period))),
            'risk' => $periods->map(fn ($period) => (int) optional($rows->first(fn ($row) => $row->period === $period && (int) $row->risk_label === 1))->total)->values(),
            'safe' => $periods->map(fn ($period) => (int) optional($rows->first(fn ($row) => $row->period === $period && (int) $row->risk_label === 0))->total)->values(),
        ];
    }

    private function classLeaderboard()
    {
        $latest = $this->latestRecords()->groupBy(fn ($log) => $log->student->class ?? '-')->map(fn ($logs) => $logs->where('risk_label', 0)->count());
        $previous = $this->previousRecords()->groupBy(fn ($log) => $log->student->class ?? '-')->map(fn ($logs) => $logs->where('risk_label', 0)->count());

        return $latest->keys()
            ->merge($previous->keys())
            ->unique()
            ->map(fn ($class) => [
                'class' => $class,
                'change' => (int) ($latest[$class] ?? 0) - (int) ($previous[$class] ?? 0),
            ])
            ->sortByDesc('change')
            ->values();
    }

    private function indicatorAverages($records): array
    {
        return collect([
            'Empathy Score' => 'empathy_score',
            'Conformity Index' => 'conformity_index',
            'Aggression Score' => 'aggression_score',
            'Emotion Stability' => 'emotion_stability',
            'Anonymity Effect' => 'anonymity_effect',
        ])->map(fn ($field, $label) => [
            'label' => $label,
            'score' => round((float) $records->avg($field), 2),
            'percent' => min(100, max(0, round((float) $records->avg($field) * 100))),
        ])->values()->all();
    }

    private function initials(string $name): string
    {
        return collect(explode(' ', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'U';
    }

    private function buildSimplePdf($logs): string
    {
        $lines = ['Dashboard Behavior Report', 'Generated: ' . now()->format('Y-m-d H:i'), 'Class: ' . ($this->classFilter ?: 'All Classes'), '', 'ID | Name | Class | Risk Label | Last Update'];

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
