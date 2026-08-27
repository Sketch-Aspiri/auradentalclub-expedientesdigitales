<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientMedicalHistoryController;
use App\Http\Controllers\PatientPhotoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('patients', PatientController::class);
    Route::put('patients/{patient}/restore', [PatientController::class, 'restore'])
        ->withTrashed()
        ->name('patients.restore');
    // withTrashed: el avatar también se muestra en el listado de pacientes archivados.
    // Sirve un binario subido por el usuario, así que se limita el ritmo por si alguien
    // intenta descargar en masa las fotos de todos los pacientes.
    Route::get('patients/{patient}/photo', PatientPhotoController::class)
        ->withTrashed()
        ->middleware('throttle:300,1')
        ->name('patients.photo');

    Route::singleton('patients.medical-history', PatientMedicalHistoryController::class)
        ->only(['edit', 'update']);

    Route::get('odontogram', [OdontogramController::class, 'index'])->name('odontogram');
    Route::get('patients/{patient}/odontogram', [OdontogramController::class, 'show'])->name('patients.odontogram');

    Route::resource('patients.consultations', ConsultationController::class)->shallow();
    Route::put('consultations/{consultation}/restore', [ConsultationController::class, 'restore'])
        ->withTrashed()
        ->name('consultations.restore');
});
