<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'role:administrador,superadmin'])
        ->get('/_test/solo-administracion', fn () => 'ok');
});

test('deniega el acceso a un doctor sobre una ruta restringida a administrador/superadmin', function () {
    // Arrange
    $doctor = User::factory()->role(UserRole::Doctor)->create();

    // Act
    $response = $this->actingAs($doctor)->get('/_test/solo-administracion');

    // Assert
    $response->assertForbidden();
});

test('permite el acceso a un administrador sobre una ruta restringida a administrador/superadmin', function () {
    // Arrange
    $admin = User::factory()->role(UserRole::Administrador)->create();

    // Act
    $response = $this->actingAs($admin)->get('/_test/solo-administracion');

    // Assert
    $response->assertOk();
});

test('permite el acceso a un superadmin sobre una ruta restringida a administrador/superadmin', function () {
    // Arrange
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();

    // Act
    $response = $this->actingAs($superadmin)->get('/_test/solo-administracion');

    // Assert
    $response->assertOk();
});
