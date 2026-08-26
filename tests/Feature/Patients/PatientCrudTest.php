<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;

test('flujo completo: un administrador da de alta, ve, edita y elimina un paciente', function () {
    // Arrange
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $this->actingAs($admin);

    // Act — alta
    $storeResponse = $this->post(route('patients.store'), [
        'full_name' => 'Roberto Carlos Méndez Ruiz',
        'birth_date' => '1975-03-20',
        'sex' => 'M',
        'phone' => '5511998877',
    ]);
    $patient = Patient::firstWhere('full_name', 'Roberto Carlos Méndez Ruiz');

    // Assert — alta
    $storeResponse->assertRedirect(route('patients.show', $patient));
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'patient_id' => $patient->id,
        'action' => 'created',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);

    // Act — ver
    $showResponse = $this->get(route('patients.show', $patient));

    // Assert — ver
    $showResponse->assertOk()->assertSee('Roberto Carlos Méndez Ruiz');
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);

    // Act — editar
    $updateResponse = $this->put(route('patients.update', $patient), [
        'full_name' => 'Roberto Carlos Méndez Ruiz',
        'birth_date' => '1975-03-20',
        'sex' => 'M',
        'phone' => '5511998800',
    ]);

    // Assert — editar
    $updateResponse->assertRedirect(route('patients.show', $patient));
    $this->assertDatabaseHas('patients', ['id' => $patient->id, 'phone' => '5511998800']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'patient_id' => $patient->id,
        'action' => 'updated',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);

    // Act — eliminar
    $destroyResponse = $this->delete(route('patients.destroy', $patient));

    // Assert — eliminar
    $destroyResponse->assertRedirect(route('patients.index'));
    $this->assertSoftDeleted($patient);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'patient_id' => $patient->id,
        'action' => 'deleted',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
});

test('el listado de pacientes busca por nombre', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($user);
    Patient::factory()->create(['full_name' => 'Ana Sofía Torres Gil']);
    Patient::factory()->create(['full_name' => 'Luis Enrique Vargas Peña']);

    // Act
    $response = $this->get(route('patients.index', ['q' => 'Torres']));

    // Assert
    $response->assertOk()
        ->assertSee('Ana Sofía Torres Gil')
        ->assertDontSee('Luis Enrique Vargas Peña');
});

test('el accessor age calcula la edad a partir de la fecha de nacimiento', function () {
    // Arrange
    $patient = Patient::factory()->create([
        'birth_date' => now()->subYears(30)->subDays(1),
    ]);

    // Act & Assert
    expect($patient->age)->toBe(30);
});

test('eliminar un paciente es un soft delete, no borra el registro', function () {
    // Arrange
    $admin = User::factory()->role(UserRole::Administrador)->create();
    $patient = Patient::factory()->create();

    // Act
    $this->actingAs($admin)->delete(route('patients.destroy', $patient));

    // Assert
    $this->assertDatabaseHas('patients', ['id' => $patient->id]);
    expect($patient->fresh()->trashed())->toBeTrue();
});
