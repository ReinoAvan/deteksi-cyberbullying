<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BehaviorLog;

class CyberbullyingDashboard extends Component
{
    public $kelas = '5A';
    public $periode = 'Semua';
    public $showModal = false;
    public $selectedStudent = '';

    public function openDetail($name) {
        $this->selectedStudent = $name;
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.cyberbullying-dashboard', [
            'logs' => BehaviorLog::orderBy('date', 'desc')->get(),
            'stats' => [
                'rendah' => 12,
                'sedang' => 3,
                'tinggi' => 2
            ]
        ]);
    }
}