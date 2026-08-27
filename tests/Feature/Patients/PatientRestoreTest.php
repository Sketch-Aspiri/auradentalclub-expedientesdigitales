<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;

test('un usuario de cualquier rol puede restaurar un paciente archivado', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();
    $patient->delete();

    // Act
    $response = $this->actingAs($user)->put(route('patients.restore', $patient));

    // Assert
    $response->assertRedirect(route('patients.show', $patient));
    expect($patient->fresh()->trashed())->toBeFalse();
})->with([
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('restaurar un paciente deja rastro en audit_logs', function () {
    // Arrange
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();
    $patient->delete();

    // Act
    $this->actingAs($admin)->put(route('patients.restore', $patient));

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'patient_id' => $patient->id,
        'action' => 'restored',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
});

test('restaurar deja exactamente un evento restored y ningún updated fantasma', function () {
    // Arrange
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();
    $patient->delete();

    // Act
    $this->actingAs($admin)->put(route('patients.restore', $patient));

    // Assert — SoftDeletes::restore() hace un save() interno; no debe contarse como edición
    $logs = fn (string $action) => AuditLog::query()
        ->where('auditable_type', Patient::class)
        ->where('auditable_id', $patient->id)
        ->where('action', $action)
        ->count();

    expect($logs('restored'))->toBe(1)
        ->and($logs('updated'))->toBe(0);
});

test('un visitante no autenticado no puede restaurar un paciente', function () {
    // Arrange
    $patient = Patient::factory()->create();
    $patient->delete();

    // Act
    $response = $this->put(route('patients.restore', $patient));

    // Assert
    $response->assertRedirect(route('login'));
    expect($patient->fresh()->trashed())->toBeTrue();
});

test('el listado de archivados muestra solo pacientes con soft delete', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create();
    $active = Patient::factory()->create(['full_name' => 'Paciente Activo Uno']);
    $archived = Patient::factory()->create(['full_name' => 'Paciente Archivado Dos']);
    $archived->delete();

    // Act
    $response = $this->actingAs($user)->get(route('patients.index', ['archived' => 1]));

    // Assert
    $response->assertOk()
        ->assertSee('Paciente Archivado Dos')
        ->assertDontSee('Paciente Activo Uno');
});
