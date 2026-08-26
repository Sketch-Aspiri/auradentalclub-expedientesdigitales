<?php

use App\Models\AuditLog;
use App\Models\User;
use Tests\Fixtures\AuditableFixture;

test('registra un audit_log al crear un modelo auditable', function () {
    // Arrange
    $actor = User::factory()->create();
    $this->actingAs($actor);

    // Act
    $record = AuditableFixture::create([
        'name' => 'Registro de prueba',
        'email' => 'auditable-fixture@auradentalclub.test',
        'password' => bcrypt('password'),
        'role' => 'doctor',
    ]);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'created',
        'auditable_type' => AuditableFixture::class,
        'auditable_id' => $record->id,
    ]);
});

test('registra un audit_log al actualizar un modelo auditable', function () {
    // Arrange
    $actor = User::factory()->create();
    $record = AuditableFixture::create([
        'name' => 'Registro de prueba',
        'email' => 'auditable-fixture-2@auradentalclub.test',
        'password' => bcrypt('password'),
        'role' => 'doctor',
    ]);
    $this->actingAs($actor);

    // Act
    $record->update(['name' => 'Registro actualizado']);

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'updated',
        'auditable_type' => AuditableFixture::class,
        'auditable_id' => $record->id,
    ]);
});

test('registra un audit_log de tipo viewed cuando se llama recordView explícitamente', function () {
    // Arrange
    $actor = User::factory()->create();
    $record = AuditableFixture::create([
        'name' => 'Registro de prueba',
        'email' => 'auditable-fixture-3@auradentalclub.test',
        'password' => bcrypt('password'),
        'role' => 'doctor',
    ]);
    $this->actingAs($actor);

    // Act
    $record->recordView();

    // Assert
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $actor->id,
        'action' => 'viewed',
        'auditable_type' => AuditableFixture::class,
        'auditable_id' => $record->id,
    ]);
});

test('no registra audit_log cuando no hay usuario autenticado', function () {
    // Act
    AuditableFixture::create([
        'name' => 'Registro sin sesión',
        'email' => 'auditable-fixture-4@auradentalclub.test',
        'password' => bcrypt('password'),
        'role' => 'doctor',
    ]);

    // Assert
    expect(AuditLog::count())->toBe(0);
});
