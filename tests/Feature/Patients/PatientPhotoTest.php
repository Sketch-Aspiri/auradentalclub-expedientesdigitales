<?php

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use App\Support\PatientPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->role(UserRole::Administrador)->create());
});

function patientPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Rosa María Guzmán Peralta',
        'birth_date' => '1990-03-15',
        'sex' => 'F',
        'phone' => '9981234567',
    ], $overrides);
}

test('sube una foto al crear un paciente y la guarda en el disco privado', function () {
    $this->post(route('patients.store'), patientPayload([
        'photo' => UploadedFile::fake()->image('rosa.jpg', 600, 600),
    ]))->assertRedirect();

    $patient = Patient::firstWhere('full_name', 'Rosa María Guzmán Peralta');

    expect($patient->photo_path)->not->toBeNull()
        ->and($patient->photo_path)->toStartWith(PatientPhoto::DIRECTORY.'/');
    Storage::disk('local')->assertExists($patient->photo_path);
});

test('el paciente se crea sin foto si no se sube ninguna', function () {
    $this->post(route('patients.store'), patientPayload())->assertRedirect();

    expect(Patient::firstWhere('full_name', 'Rosa María Guzmán Peralta')->photo_path)->toBeNull();
});

test('actualizar con una foto nueva reemplaza y borra la anterior', function () {
    $patient = Patient::factory()->withPhoto()->create();
    $oldPath = $patient->photo_path;

    $this->put(route('patients.update', $patient), patientPayload([
        'full_name' => $patient->full_name,
        'birth_date' => $patient->birth_date->format('Y-m-d'),
        'sex' => $patient->sex,
        'phone' => $patient->phone,
        'photo' => UploadedFile::fake()->image('nueva.png', 500, 500),
    ]))->assertRedirect();

    $patient->refresh();

    expect($patient->photo_path)->not->toBe($oldPath);
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($patient->photo_path);
});

test('marcar "quitar foto" borra el archivo y limpia photo_path', function () {
    $patient = Patient::factory()->withPhoto()->create();
    $oldPath = $patient->photo_path;

    $this->put(route('patients.update', $patient), patientPayload([
        'full_name' => $patient->full_name,
        'birth_date' => $patient->birth_date->format('Y-m-d'),
        'sex' => $patient->sex,
        'phone' => $patient->phone,
        'remove_photo' => '1',
    ]))->assertRedirect();

    expect($patient->refresh()->photo_path)->toBeNull();
    Storage::disk('local')->assertMissing($oldPath);
});

test('rechaza un archivo que no es imagen', function () {
    $this->post(route('patients.store'), patientPayload([
        'photo' => UploadedFile::fake()->create('historia.pdf', 200, 'application/pdf'),
    ]))->assertSessionHasErrors('photo');

    expect(Patient::count())->toBe(0);
});

test('rechaza un archivo con bytes de texto y extensión .jpg (spoof de mimetype)', function () {
    $this->post(route('patients.store'), patientPayload([
        'photo' => UploadedFile::fake()->createWithContent('rosa.jpg', 'esto no es una imagen'),
    ]))->assertSessionHasErrors('photo');

    expect(Patient::count())->toBe(0);
});

test('rechaza una imagen de más de 4 MB', function () {
    $this->post(route('patients.store'), patientPayload([
        'photo' => UploadedFile::fake()->image('enorme.jpg')->size(5000),
    ]))->assertSessionHasErrors('photo');

    expect(Patient::count())->toBe(0);
});

test('rechaza una imagen que supera los 2500 píxeles de lado', function () {
    $this->post(route('patients.store'), patientPayload([
        'photo' => UploadedFile::fake()->image('panoramica.jpg', 3000, 2000),
    ]))->assertSessionHasErrors('photo');

    expect(Patient::count())->toBe(0);
});

test('acepta una imagen WebP y la vuelve a codificar a JPEG', function () {
    $this->post(route('patients.store'), patientPayload([
        'photo' => UploadedFile::fake()->image('rosa.webp', 500, 500),
    ]))->assertRedirect()->assertSessionHasNoErrors();

    $patient = Patient::firstWhere('full_name', 'Rosa María Guzmán Peralta');

    expect($patient->photo_path)->toEndWith('.jpg');
    Storage::disk('local')->assertExists($patient->photo_path);
});

test('no se puede subir una foto y marcar "quitar foto" a la vez', function () {
    $patient = Patient::factory()->withPhoto()->create();

    $this->put(route('patients.update', $patient), patientPayload([
        'full_name' => $patient->full_name,
        'birth_date' => $patient->birth_date->format('Y-m-d'),
        'sex' => $patient->sex,
        'phone' => $patient->phone,
        'photo' => UploadedFile::fake()->image('nueva.jpg', 400, 400),
        'remove_photo' => '1',
    ]))->assertSessionHasErrors('photo');
});

test('la foto sale con cabeceras que impiden que quede en la caché del navegador', function () {
    $patient = Patient::factory()->withPhoto()->create();

    $response = $this->get(route('patients.photo', $patient));

    $response->assertOk();
    expect($response->headers->get('cache-control'))->toContain('no-store')
        ->and($response->headers->get('x-content-type-options'))->toBe('nosniff');
});

test('ver la foto se audita a lo sumo una vez por usuario y paciente al día', function () {
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    $otroDoctor = User::factory()->role(UserRole::Doctor)->create();
    $patient = Patient::factory()->withPhoto()->create();

    $this->actingAs($doctor)->get(route('patients.photo', $patient))->assertOk();
    $this->actingAs($doctor)->get(route('patients.photo', $patient))->assertOk();
    $this->actingAs($doctor)->get(route('patients.photo', $patient))->assertOk();
    $this->actingAs($otroDoctor)->get(route('patients.photo', $patient))->assertOk();

    $viewed = fn (User $user) => AuditLog::query()
        ->where('user_id', $user->id)
        ->where('patient_id', $patient->id)
        ->where('action', 'viewed')
        ->where('auditable_type', Patient::class)
        ->count();

    expect($viewed($doctor))->toBe(1)
        ->and($viewed($otroDoctor))->toBe(1);
});

test('la foto se sirve por una ruta autorizada, no por URL directa al disco', function (UserRole $role) {
    $user = User::factory()->role($role)->create();
    $patient = Patient::factory()->withPhoto()->create();

    $this->actingAs($user)
        ->get(route('patients.photo', $patient))
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
})->with([
    'doctor' => [UserRole::Doctor],
    'administrador' => [UserRole::Administrador],
    'superadmin' => [UserRole::Superadmin],
]);

test('un visitante no autenticado no puede ver la foto de un paciente', function () {
    $patient = Patient::factory()->withPhoto()->create();

    auth()->logout();

    $this->get(route('patients.photo', $patient))->assertRedirect(route('login'));
});

test('la ruta de la foto responde 404 si el paciente no tiene foto', function () {
    $patient = Patient::factory()->create();

    $this->get(route('patients.photo', $patient))->assertNotFound();
});

test('el borrado permanente del paciente elimina el archivo de su foto', function () {
    $superadmin = User::factory()->role(UserRole::Superadmin)->create();
    $this->actingAs($superadmin);
    $patient = Patient::factory()->withPhoto()->create();
    $path = $patient->photo_path;

    $patient->forceDelete();

    Storage::disk('local')->assertMissing($path);
});

test('las iniciales del paciente salen de la primera y última palabra del nombre', function () {
    expect(Patient::factory()->make(['full_name' => 'Andrés Aspiri Domínguez'])->initials)->toBe('AD')
        ->and(Patient::factory()->make(['full_name' => 'Cher'])->initials)->toBe('C');
});
