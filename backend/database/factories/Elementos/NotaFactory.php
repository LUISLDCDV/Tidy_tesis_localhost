<?php

namespace Database\Factories\Elementos;

use App\Models\Elementos\Nota;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition()
    {
        return [
            'elemento_id' => \App\Models\Elementos\Elemento::factory(),
            'fecha' => $this->faker->date(),
            'nombre' => $this->faker->sentence(3),
            'tipo_nota_id' => 1,
            'informacion' => $this->faker->paragraph(),
            'contenido' => [
                'text' => $this->faker->paragraph(),
                'tags' => $this->faker->words(3),
                'formatting' => []
            ],
            'clave' => $this->faker->optional()->word(),
        ];
    }
}