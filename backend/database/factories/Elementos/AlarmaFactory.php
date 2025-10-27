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
            'fecha' => $this->faker->date(),
            'hora' => $this->faker->time(),
            'fechaVencimiento' => $this->faker->date(),
            'horaVencimiento' => $this->faker->time(),
            'nombre' => $this->faker->sentence(3),
            'informacion' => $this->faker->paragraph(),
            'intensidad_volumen' => $this->faker->numberBetween(1, 10),
            'configuraciones' => [
                'gps' => $this->faker->latitude() . ',' . $this->faker->longitude(),
                'clima' => $this->faker->randomElement(['soleado', 'lluvioso', 'nublado']),
            ],
            // Si tienes elemento_id como clave foránea, puedes agregarlo aquí:
            // 'elemento_id' => Elemento::factory(),
        ];
    }
}