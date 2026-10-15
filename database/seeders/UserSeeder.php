<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Solo para desarrollo/pruebas académicas
        User::updateOrCreate(
            ['email' => 'admin@techstore.test'],
            ['name' => 'Administrador', 'password' => 'Admin12345', 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'cliente@techstore.test'],
            ['name' => 'Cliente de Prueba', 'password' => 'Cliente12345', 'role' => 'customer']
        );
    }
}
