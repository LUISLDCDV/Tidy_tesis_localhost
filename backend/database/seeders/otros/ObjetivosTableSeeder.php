<?php

namespace Database\Seeders;

use App\Models\Elementos\Objetivo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ObjetivosTableSeeder extends Seeder
{
    public function run()
    {
        $currentTimestamp = Carbon::now();

        $elementoIds = [18, 19, 20]; 

        $objetivos = [
            [
                'tipo' => 'personal',
                'elemento_id' => $elementoIds[0],
                'fechaCreacion' => Carbon::now()->toDateString(),
                'fechaVencimiento' => Carbon::now()->addMonths(3)->toDateString(),
                'nombre' => 'Aprender Laravel avanzado',
                'informacion' => 'Completar curso de Laravel avanzado y construir 3 proyectos',
                'status' => 'en_progreso',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'tipo' => 'profesional',
                'elemento_id' => $elementoIds[1],
                'fechaCreacion' => Carbon::now()->subMonth()->toDateString(),
                'fechaVencimiento' => Carbon::now()->addMonths(6)->toDateString(),
                'nombre' => 'Certificación en AWS',
                'informacion' => 'Prepararse y aprobar el examen de certificación AWS Solutions Architect',
                'status' => 'pendiente',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'tipo' => 'salud',
                'elemento_id' => $elementoIds[2],
                'fechaCreacion' => Carbon::now()->subDays(15)->toDateString(),
                'fechaVencimiento' => Carbon::now()->addMonths(2)->toDateString(),
                'nombre' => 'Rutina de ejercicio',
                'informacion' => 'Ejercitarse 4 veces por semana durante 2 meses',
                'status' => 'en_progreso',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'tipo' => 'financiero',
                'elemento_id' => $elementoIds[0],
                'fechaCreacion' => Carbon::now()->toDateString(),
                'fechaVencimiento' => Carbon::now()->addYear()->toDateString(),
                'nombre' => 'Ahorrar para viaje',
                'informacion' => 'Ahorrar $5,000 para viaje a Europa el próximo año',
                'status' => 'pendiente',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'tipo' => 'académico',
                'elemento_id' => $elementoIds[1],
                'fechaCreacion' => Carbon::now()->subMonths(2)->toDateString(),
                'fechaVencimiento' => Carbon::now()->addMonths(4)->toDateString(),
                'nombre' => 'Terminar maestría',
                'informacion' => 'Completar todos los créditos y tesis de maestría',
                'status' => 'completado',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ]
        ];

        // Insertar los objetivos
        DB::table('objetivos')->insert($objetivos);

        // Alternativamente puedes usar el modelo directamente:
        // foreach ($objetivos as $objetivo) {
        //     Objetivo::create($objetivo);
        // }
    }
}