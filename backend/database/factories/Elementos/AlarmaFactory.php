<?php

namespace Database\Factories\Elementos;

use App\Models\Elementos\Alarma;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlarmaFactory extends Factory
{
    protected $model = Alarma::class;

    public function definition()
    {
        return [
            'elemento_id' => \App\Models\Elementos\Elemento::factory(),
            'fecha' => $this->faker->date(),
            'hora' => $this->faker->time(),
            'fechaVencimiento' => $this->faker->date(),
            'horaVencimiento' => $this->faker->time(),
            'nombre' => $this->faker->sentence(3),
            'informacion' => $this->faker->paragraph(),
            'intensidad_volumen' => $this->faker->numberBetween(1, 10),
            'configuraciones' => [
                'gps' => [
                    'latitude' => $this->faker->latitude(),
                    'longitude' => $this->faker->longitude(),
                    'radius' => 100
                ],
                'clima' => [
                    'enabled' => true,
                    'conditions' => $this->faker->randomElement(['rain', 'storm', 'clear'])
                ],
            ],
        ];
    }
}