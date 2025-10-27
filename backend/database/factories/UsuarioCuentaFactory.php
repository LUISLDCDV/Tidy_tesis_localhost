<?php

namespace Database\Factories;

use App\Models\UsuarioCuenta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsuarioCuentaFactory extends Factory
{
    protected $model = UsuarioCuenta::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'id_medio_pago' => null, // O pon un factory si tienes el modelo
            'total_xp' => $this->faker->numberBetween(0, 1000),
            'configuraciones' => json_encode([
                'lat' => $this->faker->latitude(),
                'lng' => $this->faker->longitude(),
            ]),
        ];
    }
}