<?php

namespace Database\Factories\Elementos;

use App\Models\Elementos\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition()
    {
        return [
            'elemento_id' => \App\Models\Elementos\Elemento::factory(),
            'nombre' => $this->faker->sentence(3),
            'informacion' => $this->faker->paragraph(),
            'fechaVencimiento' => $this->faker->date(),
            'horaVencimiento' => $this->faker->time(),
            'calendario_id' => \App\Models\Elementos\Calendario::factory(),
            'metadata' => [
                'extra' => $this->faker->word(),
            ],
            'tipo' => $this->faker->randomElement(['personal', 'laboral', 'otro']),
            'gps' => [
                'latitud' => $this->faker->latitude(),
                'longitud' => $this->faker->longitude(),
            ],
            'clima' => [
                'estado' => $this->faker->randomElement(['soleado', 'lluvioso', 'nublado']),
            ],
            'recurrencia' => null,
        ];
    }
}