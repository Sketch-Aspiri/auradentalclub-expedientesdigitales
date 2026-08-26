<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Usuarios de prueba, uno por rol. Contraseña "password" para los tres — solo entorno local.
     */
    public function run(): void
    {
        User::factory()->role(UserRole::Superadmin)->create([
            'name' => 'Alejandra Rosales Villanueva',
            'email' => 'superadmin@auradentalclub.test',
        ]);

        User::factory()->role(UserRole::Administrador)->create([
            'name' => 'Fernando Iturbide Casillas',
            'email' => 'administrador@auradentalclub.test',
        ]);

        User::factory()->role(UserRole::Doctor)->create([
            'name' => 'Dra. Mariana Cabrera Solórzano',
            'email' => 'doctor@auradentalclub.test',
        ]);
    }
}
