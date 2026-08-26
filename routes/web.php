<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', App\Http\Controllers\DashboardController::class)->name('dashboard');

    Route::resource('patients', App\Http\Controllers\PatientController::class);
    Route::singleton('patients.medical-history', App\Http\Controllers\PatientMedicalHistoryController::class)
        ->only(['edit', 'update']);
});
