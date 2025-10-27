<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Cambia el namespace si tu modelo está en otro lugar
use App\Models\UsuarioCuenta;

class UsuarioAdminTableSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'password' => Hash::make('password123'), // Contraseña segura
            'phone' => '1234567890',
        ]);

        // Crear la cuenta asociada al usuario
        UsuarioCuenta::create([
            'user_id' => $user->id, // Relacionar con el usuario recién creado
            'id_medio_pago' => null,
            'configuraciones' => json_encode(['lat' => '19.432608', 'lng' => '-99.133209']),
        ]);
    }
}

