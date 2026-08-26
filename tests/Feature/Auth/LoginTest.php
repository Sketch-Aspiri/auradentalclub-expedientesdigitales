<?php

use App\Enums\UserRole;
use App\Models\User;

test('un usuario con rol doctor puede iniciar sesión y llega al dashboard', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Doctor)->create([
        'password' => bcrypt('contraseña-segura'),
    ]);

    // Act
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'contraseña-segura',
    ]);

    // Assert
    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('un usuario con rol administrador puede iniciar sesión y llega al dashboard', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Administrador)->create([
        'password' => bcrypt('contraseña-segura'),
    ]);

    // Act
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'contraseña-segura',
    ]);

    // Assert
    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('un usuario con rol superadmin puede iniciar sesión y llega al dashboard', function () {
    // Arrange
    $user = User::factory()->role(UserRole::Superadmin)->create([
        'password' => bcrypt('contraseña-segura'),
    ]);

    // Act
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'contraseña-segura',
    ]);

    // Assert
    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('rechaza el login con contraseña incorrecta', function () {
    // Arrange
    $user = User::factory()->create([
        'password' => bcrypt('contraseña-segura'),
    ]);

    // Act
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'contraseña-incorrecta',
    ]);

    // Assert
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('un usuario autenticado puede cerrar sesión', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post('/logout');

    // Assert
    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('un visitante no autenticado es redirigido al login al intentar entrar al dashboard', function () {
    // Act
    $response = $this->get('/dashboard');

    // Assert
    $response->assertRedirect(route('login'));
});
