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
            'elemento_id' => 1, // O usa un factory para Elemento
            'fecha' => $this->faker->date(),
            'nombre' => $this->faker->sentence(3),
            'tipo_nota_id' => 1,
            'informacion' => $this->faker->paragraph(),
            'contenido' => $this->faker->text(),
            'clave' => $this->faker->word(),
        ];
    }
}