<?php

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->role(UserRole::Administrador)->create());
});

test('crea un paciente con datos válidos', function () {
    // Arrange
    $data = [
        'full_name' => 'María Fernanda López Hernández',
        'birth_date' => '1988-05-12',
        'sex' => 'F',
        'occupation' => 'Contadora',
        'marital_status' => 'Casada',
        'address' => 'Av. Reforma 123, CDMX',
        'phone' => '5512345678',
        'email' => 'maria.lopez@example.com',
        'emergency_contact_name' => 'Juan López',
        'emergency_contact_phone' => '5598765432',
    ];

    // Act
    $response = $this->post(route('patients.store'), $data);

    // Assert
    $patient = Patient::firstWhere('full_name', 'María Fernanda López Hernández');
    $response->assertRedirect(route('patients.show', $patient));
    $this->assertDatabaseHas('patients', ['full_name' => 'María Fernanda López Hernández', 'phone' => '5512345678']);
});

test('rechaza la creación sin nombre completo', function () {
    // Act
    $response = $this->post(route('patients.store'), [
        'birth_date' => '1988-05-12',
        'sex' => 'F',
        'phone' => '5512345678',
    ]);

    // Assert
    $response->assertSessionHasErrors('full_name');
    $this->assertDatabaseCount('patients', 0);
});

test('rechaza la creación con una fecha de nacimiento futura', function () {
    // Act
    $response = $this->post(route('patients.store'), [
        'full_name' => 'Paciente de prueba',
        'birth_date' => now()->addYear()->format('Y-m-d'),
        'sex' => 'M',
        'phone' => '5512345678',
    ]);

    // Assert
    $response->assertSessionHasErrors('birth_date');
});

test('rechaza la creación con un sexo inválido', function () {
    // Act
    $response = $this->post(route('patients.store'), [
        'full_name' => 'Paciente de prueba',
        'birth_date' => '1990-01-01',
        'sex' => 'X',
        'phone' => '5512345678',
    ]);

    // Assert
    $response->assertSessionHasErrors('sex');
});

test('rechaza la creación con un teléfono inválido', function () {
    // Act
    $response = $this->post(route('patients.store'), [
        'full_name' => 'Paciente de prueba',
        'birth_date' => '1990-01-01',
        'sex' => 'M',
        'phone' => 'no-es-un-telefono-válido-###',
    ]);

    // Assert
    $response->assertSessionHasErrors('phone');
});

test('rechaza la creación con un correo electrónico inválido', function () {
    // Act
    $response = $this->post(route('patients.store'), [
        'full_name' => 'Paciente de prueba',
        'birth_date' => '1990-01-01',
        'sex' => 'M',
        'phone' => '5512345678',
        'email' => 'no-es-un-correo',
    ]);

    // Assert
    $response->assertSessionHasErrors('email');
});
