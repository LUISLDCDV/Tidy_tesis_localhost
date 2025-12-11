<?php

namespace Tests\Traits;

use App\Models\TipoNota;
use Illuminate\Support\Facades\DB;

trait SeedsTiposNotas
{
    /**
     * Seed tipos de notas básicos para las pruebas
     */
    protected function seedTiposNotas(): void
    {
        // Usar insert directo para garantizar que los IDs sean respetados
        DB::table('tipos_notas')->insert([
            [
                'id' => 1,
                'nombre' => 'Normal',
                'descripcion' => 'Nota de texto simple',
                'puntos_necesarios' => 0,
                'is_premium' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'nombre' => 'Planificación de Viajes',
                'descripcion' => 'Nota premium para planificar viajes',
                'puntos_necesarios' => 100,
                'is_premium' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Seed todos los tipos de notas por defecto
     */
    protected function seedAllTiposNotas(): void
    {
        TipoNota::seedTiposDefault();
    }
}
