<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('la base de datos rechaza un rol que no sea doctor, administrador o superadmin', function () {
    // Act & Assert
    expect(fn () => DB::table('users')->insert([
        'name' => 'Rol inválido',
        'email' => 'rol-invalido@auradentalclub.test',
        'password' => Hash::make('password'),
        'role' => 'recepcionista',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});
