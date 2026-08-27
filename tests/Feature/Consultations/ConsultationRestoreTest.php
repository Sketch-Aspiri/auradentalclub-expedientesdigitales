<?php

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;

test('un usuario de cualquier rol puede restaurar una consulta archivada', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $consultation = Consultation::factory()->create();
    $consultation->delete();

    // Act
    $response = $this->actingAs($user)->put(route('consultations.restore', $consultation));

    // Assert
    $response->assertRedirect(route('patients.consultations.index', $consultation->patient_id));
    expect($consultation->fresh()->trashed())->toBeFalse();
})->with([
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('restaurar una consulta deja rastro en audit_logs con el patient_id correcto', function () {
    // Arrange
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    $consultation = Consultation::factory()->for($patient)->create();
    $consultation->delete();

    // Act
    $this->actingAs($doctor)->put(route('consultations.restore', $consultation));

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'restored',
        'auditable_type' => Consultation::class,
        'auditable_id' => $consultation->id,
    ]);
});

test('un visitante no autenticado no puede restaurar una consulta', function () {
    // Arrange
    $consultation = Consultation::factory()->create();
    $consultation->delete();

    // Act
    $response = $this->put(route('consultations.restore', $consultation));

    // Assert
    $response->assertRedirect(route('login'));
    expect($consultation->fresh()->trashed())->toBeTrue();
});

test('el historial de consultas separa las archivadas de las activas', function () {
    // Arrange
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->create();
    Consultation::factory()->for($patient)->create(['clinical_diagnosis' => 'Diagnóstico activo vigente']);
    $archived = Consultation::factory()->for($patient)->create(['clinical_diagnosis' => 'Diagnóstico archivado antiguo']);
    $archived->delete();

    // Act
    $response = $this->actingAs($doctor)->get(route('patients.consultations.index', $patient));

    // Assert
    $response->assertOk()
        ->assertSee('Diagnóstico activo vigente')
        ->assertSee('Diagnóstico archivado antiguo')
        ->assertSee('Consultas archivadas');
});
