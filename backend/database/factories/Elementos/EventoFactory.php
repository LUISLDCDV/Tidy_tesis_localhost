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
            'elemento_id' => null, // Asigna un ID válido o usa Elemento::factory() si tienes el factory
            'nombre' => $this->faker->sentence(3),
            'informacion' => $this->faker->paragraph(),
            'fechaVencimiento' => $this->faker->date(),
            'horaVencimiento' => $this->faker->time(),
            'calendario_id' => null, // Asigna un ID válido o usa Calendario::factory() si tienes el factory
            'metadata' => [
                'extra' => $this->faker->word(),
            ],
            'tipo' => $this->faker->randomElement(['personal', 'laboral', 'otro']),
            
            'gps' => json_encode([
                'latitud' => $this->faker->latitude(),
                'longitud' => $this->faker->longitude(),
            ]),

            'clima' => json_encode([
                'estado' => $this->faker->randomElement(['soleado', 'lluvioso', 'nublado']),
            ]),
        ];
    }
}