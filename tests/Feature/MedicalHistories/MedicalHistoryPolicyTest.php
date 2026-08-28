<?php

use App\Enums\UserRole;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\User;

dataset('roles_con_acceso_completo', [
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('un usuario de cualquier rol puede ver/crear el formulario de historia clínica', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('patients.medical-history.edit', $patient));

    // Assert
    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede ver la pantalla de consulta de la historia clínica', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('patients.medical-history.show', $patient));

    // Assert
    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede guardar la historia clínica', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->put(route('patients.medical-history.update', $patient), [
        'has_diabetes' => '1',
        'has_hypertension' => '0',
        'has_heart_disease' => '0',
        'has_hiv_hepatitis' => '0',
        'has_coagulation_problems' => '0',
        'has_seizures' => '0',
        'has_been_hospitalized_or_operated' => '0',
        'smokes' => '0',
        'drinks_alcohol' => '0',
        'prolonged_bleeding_history' => '0',
        'weight_loss_products_history' => '0',
    ]);

    // Assert
    $response->assertRedirect(route('patients.medical-history.show', $patient));
})->with('roles_con_acceso_completo');

test('un visitante no autenticado no puede ver la historia clínica', function () {
    // Arrange
    $patient = Patient::factory()->create();

    // Act
    $response = $this->get(route('patients.medical-history.edit', $patient));

    // Assert
    $response->assertRedirect(route('login'));
});

test('un visitante no autenticado no puede ver la pantalla de consulta de la historia clínica', function () {
    // Arrange
    $patient = Patient::factory()->create();

    // Act
    $response = $this->get(route('patients.medical-history.show', $patient));

    // Assert
    $response->assertRedirect(route('login'));
});

test('solo superadmin puede eliminar permanentemente una historia clínica (forceDelete)', function () {
    // Arrange
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $administrador = User::factory()->role(UserRole::Administrador)->create();
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $medicalHistory = MedicalHistory::factory()->create();

    // Act & Assert
    expect($doctor->can('forceDelete', $medicalHistory))->toBeFalse()
        ->and($administrador->can('forceDelete', $medicalHistory))->toBeFalse()
        ->and($superadmin->can('forceDelete', $medicalHistory))->toBeTrue();
});
