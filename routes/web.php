<?php

use App\Livewire\CyberbullyingDashboard;
use App\Livewire\StudentManagement;

Route::get('/', CyberbullyingDashboard::class)->name('dashboard');

Route::get('/manajemen-siswa', StudentManagement::class)
    ->name('manajemen-siswa');