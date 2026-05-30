<?php

use App\Livewire\CyberbullyingDashboard;
use App\Livewire\BehaviorAnalysis;
use App\Livewire\LogActivity;
use App\Livewire\StudentManagement;

Route::get('/', CyberbullyingDashboard::class)
    ->name('dashboard');

Route::get('/manajemen-siswa', StudentManagement::class)
    ->name('manajemen-siswa');

Route::get('/log-activity', LogActivity::class)
    ->name('log-activity');

Route::get('/analisis-perilaku', BehaviorAnalysis::class)
    ->name('analisis-perilaku');
