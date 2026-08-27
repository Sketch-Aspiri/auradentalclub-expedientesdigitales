<?php

use App\Enums\UserRole;
use App\Livewire\Patients\PatientList;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->role(UserRole::Administrador)->create());
});

test('el listado inicial renderiza los pacientes activos ordenados por nombre', function () {
    Patient::factory()->create(['full_name' => 'Zoila Rendón Márquez']);
    Patient::factory()->create(['full_name' => 'Aarón Beltrán Ocampo']);

    Livewire::test(PatientList::class)
        ->assertOk()
        ->assertSeeInOrder(['Aarón Beltrán Ocampo', 'Zoila Rendón Márquez']);
});

test('la búsqueda en vivo filtra por nombre o teléfono y reinicia la paginación', function () {
    Patient::factory()->create(['full_name' => 'Ana Sofía Torres Gil', 'phone' => '5511112222']);
    Patient::factory()->create(['full_name' => 'Luis Enrique Vargas Peña', 'phone' => '5533334444']);

    Livewire::test(PatientList::class)
        ->set('search', 'Torres')
        ->assertSee('Ana Sofía Torres Gil')
        ->assertDontSee('Luis Enrique Vargas Peña')
        ->set('search', '5533')
        ->assertSee('Luis Enrique Vargas Peña')
        ->assertDontSee('Ana Sofía Torres Gil');
});

test('alternar a archivados muestra solo los pacientes con soft delete', function () {
    Patient::factory()->create(['full_name' => 'Paciente Activo Uno']);
    $archived = Patient::factory()->create(['full_name' => 'Paciente Archivado Dos']);
    $archived->delete();

    Livewire::test(PatientList::class)
        ->assertSee('Paciente Activo Uno')
        ->assertDontSee('Paciente Archivado Dos')
        ->call('toggleArchived')
        ->assertSee('Paciente Archivado Dos')
        ->assertDontSee('Paciente Activo Uno');
});

test('restaurar un paciente archivado desde el componente lo devuelve a la lista activa y audita', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($doctor);
    $patient = Patient::factory()->create(['full_name' => 'Ernesto Quiroga Vela']);
    $patient->delete();

    Livewire::test(PatientList::class)
        ->call('toggleArchived')
        ->call('restore', $patient->id)
        ->assertHasNoErrors();

    expect($patient->fresh()->trashed())->toBeFalse();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'restored',
        'auditable_type' => Patient::class,
    ]);
});

test('no se puede restaurar un paciente que no está archivado', function () {
    $patient = Patient::factory()->create();

    Livewire::test(PatientList::class)
        ->call('restore', $patient->id);
})->throws(ModelNotFoundException::class);

test('la paginación muestra 15 pacientes por página', function () {
    Patient::factory()->count(18)->create();

    Livewire::test(PatientList::class)
        ->assertViewHas('patients', fn ($patients) => $patients->perPage() === 15 && $patients->total() === 18)
        ->call('nextPage')
        ->assertViewHas('patients', fn ($patients) => $patients->currentPage() === 2);
});

test('el listado muestra el doctor de la consulta más reciente de cada paciente', function () {
    $doctorAntiguo = User::factory()->role(UserRole::Doctor)->create(['name' => 'Dra. Prueba Antigua']);
    $doctorReciente = User::factory()->role(UserRole::Doctor)->create(['name' => 'Dr. Prueba Reciente']);

    $conConsultas = Patient::factory()->create(['full_name' => 'Paciente Con Consultas']);
    Consultation::factory()->for($conConsultas)->create([
        'doctor_id' => $doctorAntiguo->id,
        'consultation_date' => now()->subYear()->format('Y-m-d'),
    ]);
    Consultation::factory()->for($conConsultas)->create([
        'doctor_id' => $doctorReciente->id,
        'consultation_date' => now()->subMonth()->format('Y-m-d'),
    ]);

    $sinConsultas = Patient::factory()->create(['full_name' => 'Paciente Sin Consultas']);

    Livewire::test(PatientList::class)
        ->assertSee('Dr. Prueba Reciente')
        ->assertDontSee('Dra. Prueba Antigua')
        ->assertSeeInOrder(['Paciente Sin Consultas', '—']);
});

test('un visitante no autenticado no puede montar el listado', function () {
    auth()->logout();

    Livewire::test(PatientList::class)->assertForbidden();
});

test('la ruta patients.index sigue respondiendo y respeta ?q= y ?archived= en el render inicial', function () {
    Patient::factory()->create(['full_name' => 'Marisol Cárdenas Ibáñez']);
    $archived = Patient::factory()->create(['full_name' => 'Gustavo Prieto Salas']);
    $archived->delete();

    $this->get(route('patients.index', ['q' => 'Marisol']))
        ->assertOk()
        ->assertSee('Marisol Cárdenas Ibáñez')
        ->assertDontSee('Gustavo Prieto Salas');

    $this->get(route('patients.index', ['archived' => 1]))
        ->assertOk()
        ->assertSee('Gustavo Prieto Salas')
        ->assertDontSee('Marisol Cárdenas Ibáñez');
});
