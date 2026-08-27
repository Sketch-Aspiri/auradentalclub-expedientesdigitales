<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Support\UserAvatar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('un usuario autenticado ve su pantalla de perfil', function () {
    $user = User::factory()->role(UserRole::Doctor)->create(['name' => 'Dra. Prueba']);

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Dra. Prueba')
        ->assertSee('Mi perfil');
});

test('un visitante no autenticado no puede abrir el perfil', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->put(route('profile.update'), [])->assertRedirect(route('login'));
    $this->put(route('profile.password'), [])->assertRedirect(route('login'));
});

test('el usuario puede cambiar su nombre sin confirmar la contraseña', function () {
    $user = User::factory()->create(['name' => 'Nombre Viejo', 'email' => 'user@auradentalclub.test']);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Nombre Nuevo',
        'email' => 'user@auradentalclub.test',
    ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');

    expect($user->fresh()->name)->toBe('Nombre Nuevo');
});

test('cambiar solo el nombre funciona aunque el formulario envíe current_password vacío', function () {
    $user = User::factory()->create(['name' => 'Nombre Viejo', 'email' => 'user@auradentalclub.test']);

    // El <form> siempre manda el campo, vacío si no se escribió nada.
    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Nombre Nuevo',
        'email' => 'user@auradentalclub.test',
        'current_password' => '',
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Nombre Nuevo');
});

test('cambiar el correo exige confirmar la contraseña actual', function () {
    $user = User::factory()->create(['email' => 'antes@auradentalclub.test']);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => 'despues@auradentalclub.test',
    ])->assertSessionHasErrors('current_password', null, 'updateProfile');

    expect($user->fresh()->email)->toBe('antes@auradentalclub.test');
});

test('el correo se actualiza si la contraseña actual es correcta', function () {
    $user = User::factory()->create(['email' => 'antes@auradentalclub.test']);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => 'despues@auradentalclub.test',
        'current_password' => 'password',
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

    expect($user->fresh()->email)->toBe('despues@auradentalclub.test');
});

test('el correo no se actualiza con una contraseña actual incorrecta', function () {
    $user = User::factory()->create(['email' => 'antes@auradentalclub.test']);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => 'despues@auradentalclub.test',
        'current_password' => 'no-es-la-contraseña',
    ])->assertSessionHasErrors('current_password', null, 'updateProfile');

    expect($user->fresh()->email)->toBe('antes@auradentalclub.test');
});

test('no se puede tomar el correo de otro usuario', function () {
    User::factory()->create(['email' => 'ocupado@auradentalclub.test']);
    $user = User::factory()->create(['email' => 'propio@auradentalclub.test']);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => 'ocupado@auradentalclub.test',
        'current_password' => 'password',
    ])->assertSessionHasErrors('email', null, 'updateProfile');
});

test('el rol no se puede cambiar desde el perfil', function () {
    $user = User::factory()->role(UserRole::Doctor)->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'role' => UserRole::Superadmin->value,
    ])->assertRedirect(route('profile.edit'));

    expect($user->fresh()->role)->toBe(UserRole::Doctor);
});

test('el usuario cambia su contraseña con la actual y confirmación válidas', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'una-contraseña-nueva-larga',
        'password_confirmation' => 'una-contraseña-nueva-larga',
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

    expect(Hash::check('una-contraseña-nueva-larga', $user->fresh()->password))->toBeTrue();
});

test('el cambio de contraseña falla con la contraseña actual incorrecta', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'incorrecta',
        'password' => 'una-contraseña-nueva-larga',
        'password_confirmation' => 'una-contraseña-nueva-larga',
    ])->assertSessionHasErrors('current_password', null, 'updatePassword');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('el cambio de contraseña falla si la confirmación no coincide', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'una-contraseña-nueva-larga',
        'password_confirmation' => 'otra-cosa',
    ])->assertSessionHasErrors('password', null, 'updatePassword');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('el cambio de contraseña falla si la nueva es demasiado corta', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'corta',
        'password_confirmation' => 'corta',
    ])->assertSessionHasErrors('password', null, 'updatePassword');
});

test('el usuario sube su foto de perfil y se guarda en el disco privado', function () {
    Storage::fake('local');
    $user = User::factory()->create(['name' => 'Alejandra Rosales', 'email' => 'ale@auradentalclub.test']);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Alejandra Rosales',
        'email' => 'ale@auradentalclub.test',
        'photo' => UploadedFile::fake()->image('yo.jpg', 500, 500),
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->photo_path)->toStartWith(UserAvatar::DIRECTORY.'/')
        ->and($user->photo_path)->toEndWith('.jpg');
    Storage::disk('local')->assertExists($user->photo_path);
});

test('subir una foto nueva reemplaza y borra la anterior', function () {
    Storage::fake('local');
    $user = User::factory()->withPhoto()->create();
    $old = $user->photo_path;

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('nueva.png', 400, 400),
    ])->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->photo_path)->not->toBe($old);
    Storage::disk('local')->assertMissing($old);
    Storage::disk('local')->assertExists($user->photo_path);
});

test('marcar "quitar foto" borra el archivo y limpia photo_path', function () {
    Storage::fake('local');
    $user = User::factory()->withPhoto()->create();
    $old = $user->photo_path;

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'remove_photo' => '1',
    ])->assertRedirect(route('profile.edit'));

    expect($user->fresh()->photo_path)->toBeNull();
    Storage::disk('local')->assertMissing($old);
});

test('rechaza un archivo que no es imagen como foto de perfil', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('photo', null, 'updateProfile');
});

test('rechaza una foto de perfil que supera los 2500 píxeles de lado', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('grande.jpg', 3000, 3000),
    ])->assertSessionHasErrors('photo', null, 'updateProfile');
});

test('acepta una foto de perfil WebP y la vuelve a codificar a JPEG', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('yo.webp', 500, 500),
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

    expect($user->fresh()->photo_path)->toEndWith('.jpg');
});

test('no se puede subir una foto de perfil y marcar "quitar foto" a la vez', function () {
    Storage::fake('local');
    $user = User::factory()->withPhoto()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('nueva.jpg', 400, 400),
        'remove_photo' => '1',
    ])->assertSessionHasErrors('photo', null, 'updateProfile');
});

test('la foto de perfil se sirve por una ruta autorizada con cabeceras anti-caché', function () {
    Storage::fake('local');
    $user = User::factory()->withPhoto()->create();

    $response = $this->actingAs($user)->get(route('profile.photo'));

    $response->assertOk();
    expect($response->headers->get('cache-control'))->toContain('no-store')
        ->and($response->headers->get('x-content-type-options'))->toBe('nosniff');
});

test('un visitante no autenticado no puede ver una foto de perfil', function () {
    $this->get(route('profile.photo'))->assertRedirect(route('login'));
});

test('la ruta de la foto de perfil responde 404 si el usuario no tiene foto', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile.photo'))->assertNotFound();
});

test('cada usuario solo puede ver su propia foto de perfil', function () {
    Storage::fake('local');
    $conFoto = User::factory()->withPhoto()->create();
    $sinFoto = User::factory()->create();

    // El endpoint sirve la foto de la cuenta autenticada, no de otra: el que no tiene foto obtiene 404.
    $this->actingAs($sinFoto)->get(route('profile.photo'))->assertNotFound();
    $this->actingAs($conFoto)->get(route('profile.photo'))->assertOk();
});
