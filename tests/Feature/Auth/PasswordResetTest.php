<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('envía un enlace de restablecimiento cuando el correo existe', function () {
    // Arrange
    Notification::fake();
    $user = User::factory()->create();

    // Act
    $response = $this->post('/forgot-password', ['email' => $user->email]);

    // Assert
    $response->assertSessionHas('status');
    Notification::assertSentTo($user, ResetPassword::class);
});

test('permite restablecer la contraseña con un token válido', function () {
    // Arrange
    Notification::fake();
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email]);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    // Act
    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'nueva-contraseña-segura',
        'password_confirmation' => 'nueva-contraseña-segura',
    ]);

    // Assert
    $response->assertRedirect(route('login'));
    $this->assertTrue(auth()->attempt([
        'email' => $user->email,
        'password' => 'nueva-contraseña-segura',
    ]));
});
