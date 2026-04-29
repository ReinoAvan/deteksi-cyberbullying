<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;

class StudentManagement extends Component
{
    public $students;

    public $name, $nis, $class, $gender, $status, $risk_level;
    public $editId = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->students = Student::latest()->get();
    }

    public function save()
    {
        $data = [
            'name' => $this->name,
            'nis' => $this->nis,
            'class' => $this->class,
            'gender' => $this->gender,
            'status' => $this->status,
            'risk_level' => $this->risk_level,
        ];

        if ($this->editId) {
            Student::find($this->editId)->update($data);
        } else {
            Student::create($data);
        }

        $this->resetForm();
        $this->loadData();
    }

    public function edit($id)
    {
        $s = Student::find($id);

        $this->editId = $id;
        $this->name = $s->name;
        $this->nis = $s->nis;
        $this->class = $s->class;
        $this->gender = $s->gender;
        $this->status = $s->status;
        $this->risk_level = $s->risk_level;
    }

    public function delete($id)
    {
        Student::find($id)->delete();
        $this->loadData();
    }

    public function resetForm()
    {
        $this->reset([
            'name','nis','class','gender','status','risk_level','editId'
        ]);
    }

    public function render()
    {
        return view('livewire.student-management')
            ->layout('layouts.app');
    }
}