<?php

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\User;

test('la vista de impresión muestra los datos del consentimiento y el placeholder de texto legal', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $consent = Consent::factory()->extraction()->create();

    $this->actingAs($doctor)->get(route('consents.print', $consent))
        ->assertOk()
        ->assertSee('CONSENTIMIENTO INFORMADO')
        ->assertSee('LEY GENERAL DE SALUD')
        ->assertSee('NOM-013-SSA2-2015')
        ->assertSee('Plan de tratamiento');
});

test('la vista de impresión de un consentimiento anulado muestra el sello ANULADO', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $consent = Consent::factory()->voided()->create();

    $this->actingAs($doctor)->get(route('consents.print', $consent))
        ->assertOk()
        ->assertSee('ANULADO');
});

test('un visitante no autenticado no puede imprimir un consentimiento', function () {
    $consent = Consent::factory()->create();

    $this->get(route('consents.print', $consent))->assertRedirect(route('login'));
});
