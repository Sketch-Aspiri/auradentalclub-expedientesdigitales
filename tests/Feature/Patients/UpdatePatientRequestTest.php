<?php

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->role(UserRole::Administrador)->create());
    $this->patient = Patient::factory()->create(['full_name' => 'Nombre Original']);
});

test('actualiza un paciente con datos válidos', function () {
    // Act
    $response = $this->put(route('patients.update', $this->patient), [
        'full_name' => 'Nombre Actualizado López',
        'birth_date' => '1990-01-01',
        'sex' => 'F',
        'phone' => '5544332211',
    ]);

    // Assert
    $response->assertRedirect(route('patients.show', $this->patient));
    $this->assertDatabaseHas('patients', ['id' => $this->patient->id, 'full_name' => 'Nombre Actualizado López']);
});

test('rechaza la actualización sin nombre completo', function () {
    // Act
    $response = $this->put(route('patients.update', $this->patient), [
        'birth_date' => '1990-01-01',
        'sex' => 'F',
        'phone' => '5544332211',
    ]);

    // Assert
    $response->assertSessionHasErrors('full_name');
    $this->assertDatabaseHas('patients', ['id' => $this->patient->id, 'full_name' => 'Nombre Original']);
});

test('rechaza la actualización con una fecha de nacimiento futura', function () {
    // Act
    $response = $this->put(route('patients.update', $this->patient), [
        'full_name' => 'Nombre Original',
        'birth_date' => now()->addMonth()->format('Y-m-d'),
        'sex' => 'F',
        'phone' => '5544332211',
    ]);

    // Assert
    $response->assertSessionHasErrors('birth_date');
});

test('rechaza la actualización con un sexo inválido', function () {
    // Act
    $response = $this->put(route('patients.update', $this->patient), [
        'full_name' => 'Nombre Original',
        'birth_date' => '1990-01-01',
        'sex' => 'Otro',
        'phone' => '5544332211',
    ]);

    // Assert
    $response->assertSessionHasErrors('sex');
});

test('rechaza la actualización con un teléfono inválido', function () {
    // Act
    $response = $this->put(route('patients.update', $this->patient), [
        'full_name' => 'Nombre Original',
        'birth_date' => '1990-01-01',
        'sex' => 'F',
        'phone' => '###',
    ]);

    // Assert
    $response->assertSessionHasErrors('phone');
});
