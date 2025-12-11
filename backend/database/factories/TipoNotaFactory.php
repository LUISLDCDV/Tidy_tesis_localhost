<?php

namespace Database\Factories;

use App\Models\TipoNota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoNota>
 */
class TipoNotaFactory extends Factory
{
    protected $model = TipoNota::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->words(2, true),
            'descripcion' => fake()->sentence(),
            'puntos_necesarios' => fake()->numberBetween(0, 100),
            'is_premium' => false,
        ];
    }

    /**
     * Indicate that the tipo nota is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
            'puntos_necesarios' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * Indicate that the tipo nota is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => false,
            'puntos_necesarios' => 0,
        ]);
    }
}
