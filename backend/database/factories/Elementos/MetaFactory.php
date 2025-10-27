<?php

namespace Database\Factories\Elementos;

use App\Models\Elementos\Meta;
use Illuminate\Database\Eloquent\Factories\Factory;

class MetaFactory extends Factory
{
    protected $model = Meta::class;

    public function definition()
    {
        return [
            'elemento_id' => null, // O usa \App\Models\Elementos\Elemento::factory()
            'tipo' => $this->faker->randomElement(['personal', 'profesional', 'otro']),
            'fechaCreacion' => $this->faker->date(),
            'fechaVencimiento' => $this->faker->optional()->date(),
            'nombre' => $this->faker->words(3, true),
            'informacion' => $this->faker->sentence(),
            'objetivo_id' => null, // O usa \App\Models\Elementos\Objetivo::factory()
            'status' => $this->faker->randomElement(['pendiente', 'en progreso', 'completada']),
        ];
    }
}