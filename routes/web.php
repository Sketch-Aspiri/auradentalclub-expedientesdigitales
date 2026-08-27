<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientMedicalHistoryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('patients', PatientController::class);
    Route::put('patients/{patient}/restore', [PatientController::class, 'restore'])
        ->withTrashed()
        ->name('patients.restore');

    Route::singleton('patients.medical-history', PatientMedicalHistoryController::class)
        ->only(['edit', 'update']);

    Route::get('patients/{patient}/odontogram', OdontogramController::class)->name('patients.odontogram');

    Route::resource('patients.consultations', ConsultationController::class)->shallow();
    Route::put('consultations/{consultation}/restore', [ConsultationController::class, 'restore'])
        ->withTrashed()
        ->name('consultations.restore');
});
