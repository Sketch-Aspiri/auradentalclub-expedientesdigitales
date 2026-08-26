<?php

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

dataset('roles_con_acceso_completo', [
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('un usuario de cualquier rol puede listar pacientes', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();

    // Act
    $response = $this->actingAs($user)->get(route('patients.index'));

    // Assert
    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede ver el formulario de alta de paciente', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();

    // Act
    $response = $this->actingAs($user)->get(route('patients.create'));

    // Assert
    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede ver la ficha de un paciente', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('patients.show', $patient));

    // Assert
    $response->assertOk();
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede editar un paciente', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->put(route('patients.update', $patient), [
        'full_name' => 'Nombre Actualizado',
        'birth_date' => '1990-01-01',
        'sex' => 'M',
        'phone' => '5511223344',
    ]);

    // Assert
    $response->assertRedirect(route('patients.show', $patient));
})->with('roles_con_acceso_completo');

test('un usuario de cualquier rol puede eliminar un paciente', function (UserRole $role) {
    // Arrange
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->create();

    // Act
    $response = $this->actingAs($user)->delete(route('patients.destroy', $patient));

    // Assert
    $response->assertRedirect(route('patients.index'));
    $this->assertSoftDeleted($patient);
})->with('roles_con_acceso_completo');

test('un visitante no autenticado no puede acceder al listado de pacientes', function () {
    // Act
    $response = $this->get(route('patients.index'));

    // Assert
    $response->assertRedirect(route('login'));
});

test('solo superadmin puede eliminar permanentemente a un paciente (forceDelete)', function () {
    // Arrange
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $administrador = User::factory()->role(UserRole::Administrador)->create();
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $patient = Patient::factory()->create();

    // Act & Assert
    expect($doctor->can('forceDelete', $patient))->toBeFalse()
        ->and($administrador->can('forceDelete', $patient))->toBeFalse()
        ->and($superadmin->can('forceDelete', $patient))->toBeTrue();
});
