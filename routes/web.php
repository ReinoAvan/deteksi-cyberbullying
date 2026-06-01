<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Livewire\CyberbullyingDashboard;
use App\Livewire\BehaviorAnalysis;
use App\Livewire\LogActivity;
use App\Livewire\StudentManagement;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', CyberbullyingDashboard::class)
        ->name('dashboard');

    Route::get('/manajemen-siswa', StudentManagement::class)
        ->name('manajemen-siswa');

    Route::get('/log-activity', LogActivity::class)
        ->name('log-activity');

    Route::get('/analisis-perilaku', BehaviorAnalysis::class)
        ->name('analisis-perilaku');
});
