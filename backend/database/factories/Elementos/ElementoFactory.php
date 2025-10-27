<?php

namespace Database\Factories\Elementos;


use App\Models\Elementos\Alarma;
use App\Models\Elementos\Objetivo;
use App\Models\Elementos\Meta;
use App\Models\Elementos\Calendario;
use App\Models\Elementos\Evento;
use App\Models\Elementos\Nota;
use App\Models\Elementos\Elemento;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ElementoFactory extends Factory
{
    protected $model = Elemento::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'descripcion' => $this->faker->sentence(4),
            'cuenta_id' => 1, // O usa un factory para Cuenta si tienes relación
            'tipo' => $this->faker->randomElement(['alarma', 'objetivo', 'meta', 'calendario', 'evento', 'nota']),
            // Agrega aquí otros campos si tu tabla los tiene
        ];
    }

}
