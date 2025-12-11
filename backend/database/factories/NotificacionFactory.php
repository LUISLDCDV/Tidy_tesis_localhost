<?php

namespace Database\Factories;

use App\Models\Notificacion;
use App\Models\User;
use App\Models\Elementos\Elemento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notificacion>
 */
class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipos = ['recordatorio', 'alarma', 'evento', 'objetivo', 'general'];

        return [
            'usuario_id' => User::factory(),
            'elemento_id' => null, // Puede ser null o se puede asignar manualmente
            'tipo' => fake()->randomElement($tipos),
            'descripcion' => fake()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the notification is of type 'recordatorio'.
     */
    public function recordatorio(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'recordatorio',
        ]);
    }

    /**
     * Indicate that the notification is of type 'alarma'.
     */
    public function alarma(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'alarma',
        ]);
    }

    /**
     * Indicate that the notification is of type 'evento'.
     */
    public function evento(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'evento',
        ]);
    }
}
