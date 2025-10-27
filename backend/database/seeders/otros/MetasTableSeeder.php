<?php

namespace Database\Seeders;

use App\Models\Elementos\Meta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class MetasTableSeeder extends Seeder
{
    public function run()
    {
        $currentTimestamp = Carbon::now();

        $elementoIds = range(21, 26);
    
        $objetivoIds = range(1, 3);

        $metas = [
            [
                'elemento_id' => $elementoIds[0],
                'tipo' => 'semanal',
                'fechaCreacion' => Carbon::now()->toDateString(),
                'fechaVencimiento' => Carbon::now()->addWeek()->toDateString(),
                'nombre' => 'Completar módulo de autenticación',
                'informacion' => 'Implementar login, registro y recuperación de contraseña',
                'objetivo_id' => $objetivoIds[0],
                'status' => 'pendiente',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[1],
                'tipo' => 'mensual',
                'fechaCreacion' => Carbon::now()->subDays(10)->toDateString(),
                'fechaVencimiento' => Carbon::now()->addMonth()->toDateString(),
                'nombre' => 'Leer 2 libros técnicos',
                'informacion' => 'Clean Code y Design Patterns',
                'objetivo_id' => $objetivoIds[1],
                'status' => 'en_progreso',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[2],
                'tipo' => 'trimestral',
                'fechaCreacion' => Carbon::now()->subMonth()->toDateString(),
                'fechaVencimiento' => Carbon::now()->addMonths(2)->toDateString(),
                'nombre' => 'Perder 5 kg',
                'informacion' => 'Dieta y ejercicio 4 veces por semana',
                'objetivo_id' => $objetivoIds[2],
                'status' => 'en_progreso',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
        

        ];

        // Insertar las metas
        DB::table('metas')->insert($metas);

        // Alternativa usando el modelo (mejor para triggers/observers)
        // foreach ($metas as $meta) {
        //     Meta::create($meta);
        // }
    }
}