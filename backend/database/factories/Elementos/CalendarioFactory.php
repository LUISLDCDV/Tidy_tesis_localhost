<?php

namespace Database\Factories\Elementos;

use App\Models\Elementos\Calendario;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarioFactory extends Factory
{
    protected $model = Calendario::class;

    public function definition()
    {
        return [
            'elemento_id' => null, // Asigna un ID válido o usa Elemento::factory() si tienes el factory
            'nombre' => $this->faker->words(2, true),
            'color' => $this->faker->safeHexColor(),
            'informacion' => $this->faker->sentence(),
        ];
    }
}