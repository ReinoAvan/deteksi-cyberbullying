<?php

namespace App\Livewire;

use App\Models\LogActivity as LogActivityModel;
use Livewire\Component;
use Livewire\WithPagination;

class LogActivity extends Component
{
    use WithPagination;

    public string $search = '';
    public string $classFilter = '';
    public string $sortField = 'last_update';
    public string $sortDirection = 'desc';
    public string $perPage = '10';

    public bool $showDetailModal = false;
    public ?LogActivityModel $selectedLogActivity = null;

    protected array $queryString = [
        'search' => ['except' => ''],
        'classFilter' => ['except' => ''],
        'sortField' => ['except' => 'last_update'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => '10'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['student_id', 'name', 'class', 'response_time_mean', 'last_update'], true)) {
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

    public function showDetail(int $id): void
    {
        $this->selectedLogActivity = LogActivityModel::with('student')->findOrFail($id);
        $this->showDetailModal = true;
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

    public function getFastestResponseTimeProperty(): float
    {
        return (float) LogActivityModel::min('response_time_mean');
    }

    public function getLongestResponseTimeProperty(): float
    {
        return (float) LogActivityModel::max('response_time_mean');
    }

    public function getAverageResponseTimeProperty(): float
    {
        return (float) LogActivityModel::avg('response_time_mean');
    }

    public function getClassOptionsProperty()
    {
        return LogActivityModel::query()
            ->join('students', 'log_activities.student_id', '=', 'students.nis')
            ->select('students.class')
            ->distinct()
            ->orderBy('students.class')
            ->pluck('students.class');
    }

    public function getSelectedLatestActivityProperty(): ?LogActivityModel
    {
        if (! $this->selectedLogActivity) {
            return null;
        }

        return LogActivityModel::where('student_id', $this->selectedLogActivity->student_id)
            ->orderByDesc('last_update')
            ->first();
    }

    public function getSelectedActivityHistoryProperty()
    {
        if (! $this->selectedLogActivity) {
            return collect();
        }

        return LogActivityModel::where('student_id', $this->selectedLogActivity->student_id)
            ->where('id', '!=', $this->selectedLatestActivity?->id)
            ->orderByDesc('last_update')
            ->get();
    }

    public function render()
    {
        $query = $this->filteredQuery();
        $logs = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $this->perPage);

        return view('livewire.log-activity', [
            'logs' => $logs,
            'classOptions' => $this->classOptions,
            'totalActivities' => $this->totalActivities,
            'fastestResponseTime' => $this->fastestResponseTime,
            'longestResponseTime' => $this->longestResponseTime,
            'averageResponseTime' => $this->averageResponseTime,
            'selectedLatestActivity' => $this->selectedLatestActivity,
            'selectedActivityHistory' => $this->selectedActivityHistory,
        ])->layout('layouts.app');
    }

    private function filteredQuery()
    {
        $query = LogActivityModel::query()
            ->with('student')
            ->leftJoin('students', 'log_activities.student_id', '=', 'students.nis')
            ->select('log_activities.*')
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('log_activities.student_id', 'like', $search)
                        ->orWhere('log_activities.name', 'like', $search)
                        ->orWhere('students.name', 'like', $search);
                });
            })
            ->when($this->classFilter !== '', fn ($query) => $query->where('students.class', $this->classFilter));

        if ($this->sortField === 'class') {
            return $query->orderBy('students.class', $this->sortDirection);
        }

        return $query->orderBy('log_activities.' . $this->sortField, $this->sortDirection);
    }
}
