<?php

namespace Database\Factories\Elementos;

use App\Models\Elementos\Objetivo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObjetivoFactory extends Factory
{
    protected $model = Objetivo::class;

    public function definition()
    {
        return [
            'tipo' => $this->faker->randomElement(['personal', 'profesional', 'otro']),
            'elemento_id' => null, // O usa \App\Models\Elementos\Elemento::factory()
            'fechaCreacion' => $this->faker->date(),
            'fechaVencimiento' => $this->faker->optional()->date(),
            'nombre' => $this->faker->words(3, true),
            'informacion' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pendiente', 'en progreso', 'completado']),
        ];
    }
}