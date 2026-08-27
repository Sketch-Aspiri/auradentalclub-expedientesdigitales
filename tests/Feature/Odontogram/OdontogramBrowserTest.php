<?php

use App\Enums\UserRole;
use App\Livewire\Odontogram\Browser;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

dataset('roles_con_acceso_completo', [
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('la pantalla global del odontograma abre para cualquier rol', function (UserRole $role) {
    $this->actingAs(User::factory()->role($role)->create())
        ->get(route('odontogram'))
        ->assertOk()
        ->assertSee('Busca un paciente');
})->with('roles_con_acceso_completo');

test('un visitante no autenticado no puede abrir la pantalla global del odontograma', function () {
    $this->get(route('odontogram'))->assertRedirect(route('login'));
});

test('la búsqueda filtra pacientes por nombre o teléfono', function () {
    $this->actingAs(User::factory()->role(UserRole::Doctor)->create());
    Patient::factory()->create(['full_name' => 'Beatriz Salazar Mora', 'phone' => '9981112233']);
    Patient::factory()->create(['full_name' => 'Raúl Contreras Fuentes', 'phone' => '9984445566']);

    Livewire::test(Browser::class)
        ->set('search', 'Salazar')
        ->assertSee('Beatriz Salazar Mora')
        ->assertDontSee('Raúl Contreras Fuentes')
        ->set('search', '9984445566')
        ->assertSee('Raúl Contreras Fuentes')
        ->assertDontSee('Beatriz Salazar Mora');
});

test('elegir un paciente muestra su odontograma y audita el acceso al expediente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($doctor);
    $patient = Patient::factory()->create(['full_name' => 'Verónica Núñez Ballesteros']);

    Livewire::test(Browser::class)
        ->call('selectPatient', $patient->id)
        ->assertSet('patientId', $patient->id)
        ->assertSeeLivewire('patients.odontogram');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
        'auditable_type' => Patient::class,
        'auditable_id' => $patient->id,
    ]);
});

test('el parámetro ?paciente= carga directamente el odontograma de ese paciente', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $this->actingAs($doctor);
    $patient = Patient::factory()->create();

    Livewire::withUrlParams(['paciente' => $patient->id])
        ->test(Browser::class)
        ->assertSet('patientId', $patient->id)
        ->assertSeeLivewire('patients.odontogram');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'viewed',
    ]);
});

test('un ?paciente= inexistente se ignora y se muestra el buscador', function () {
    $this->actingAs(User::factory()->role(UserRole::Doctor)->create());

    Livewire::withUrlParams(['paciente' => 999999])
        ->test(Browser::class)
        ->assertSet('patientId', null)
        ->assertSee('Busca un paciente');
});

test('elegir un id de paciente inexistente lanza 404', function () {
    $this->actingAs(User::factory()->role(UserRole::Doctor)->create());

    Livewire::test(Browser::class)->call('selectPatient', 999999);
})->throws(ModelNotFoundException::class);

test('un visitante no autenticado no puede montar el componente', function () {
    Livewire::test(Browser::class)->assertForbidden();
});

test('no se puede fijar patientId desde el cliente para esquivar autorización y auditoría', function () {
    $this->actingAs(User::factory()->role(UserRole::Doctor)->create());
    $patient = Patient::factory()->create();

    Livewire::test(Browser::class)->set('patientId', $patient->id);
})->throws(Exception::class, 'Cannot update locked property');

test('cambiar de paciente limpia la selección y vuelve al buscador', function () {
    $this->actingAs(User::factory()->role(UserRole::Doctor)->create());
    $patient = Patient::factory()->create();

    Livewire::test(Browser::class)
        ->call('selectPatient', $patient->id)
        ->assertSet('patientId', $patient->id)
        ->call('clearPatient')
        ->assertSet('patientId', null)
        ->assertSee('Busca un paciente');
});
