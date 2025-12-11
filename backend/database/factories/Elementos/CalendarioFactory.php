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
            'elemento_id' => \App\Models\Elementos\Elemento::factory(),
            'nombre' => $this->faker->words(2, true),
            'color' => $this->faker->safeHexColor(),
            'informacion' => $this->faker->sentence(),
        ];
    }
}