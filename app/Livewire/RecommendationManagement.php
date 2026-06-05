<?php

namespace App\Livewire;

use App\Models\Recommendation;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class RecommendationManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'nama_sikap';
    public string $sortDirection = 'asc';
    public string $perPage = '10';
    public bool $showFormModal = false;
    public bool $showDetailModal = false;
    public ?int $editId = null;
    public ?Recommendation $selectedRecommendation = null;
    public string $nama_sikap = 'empathy';
    public string $uraian_rekomendasi = '';

    public array $behaviorOptions = [
        'empathy',
        'conformity',
        'aggression',
        'emotion_stability',
        'anonymity_effect',
    ];

    protected array $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nama_sikap'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => '10'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['nama_sikap', 'created_at'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $recommendation = Recommendation::findOrFail($id);
        $this->editId = $recommendation->id;
        $this->nama_sikap = $recommendation->nama_sikap;
        $this->uraian_rekomendasi = $recommendation->uraian_rekomendasi;
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function showDetail(int $id): void
    {
        $this->selectedRecommendation = Recommendation::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'nama_sikap' => ['required', Rule::in($this->behaviorOptions)],
            'uraian_rekomendasi' => ['required', 'string'],
        ]);

        Recommendation::updateOrCreate(['id' => $this->editId], $validated);

        $this->showFormModal = false;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Recommendation saved successfully.');
    }

    public function delete(int $id): void
    {
        Recommendation::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Recommendation deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->nama_sikap = 'empathy';
        $this->uraian_rekomendasi = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = Recommendation::query()
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';
                $query->where('nama_sikap', 'like', $search)
                    ->orWhere('uraian_rekomendasi', 'like', $search);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $recommendations = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $this->perPage);

        return view('livewire.recommendation-management', [
            'recommendations' => $recommendations,
            'totalRecommendations' => Recommendation::count(),
        ])->layout('layouts.app');
    }
}
