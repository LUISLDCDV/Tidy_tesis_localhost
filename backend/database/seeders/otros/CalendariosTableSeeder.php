<?php

namespace Database\Seeders;

use App\Models\Elementos\Calendario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CalendariosTableSeeder extends Seeder
{
    public function run()
    {
        $currentTimestamp = Carbon::now();
        
        $elementoIds = range(6, 11);

        $calendarios = [
            [
                'elemento_id' => $elementoIds[0],
                'nombre' => 'Personal',
                'color' => 1, // Ej: 1=Azul, 2=Rojo, etc.
                'informacion' => 'Calendario para actividades personales',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[1],
                'nombre' => 'Trabajo',
                'color' => 2,
                'informacion' => 'Calendario profesional y reuniones',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[2],
                'nombre' => 'Familiar',
                'color' => 3,
                'informacion' => 'Eventos familiares y cumpleaños',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[3],
                'nombre' => 'Estudios',
                'color' => 4,
                'informacion' => 'Calendario académico y cursos',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[4],
                'nombre' => 'Fitness',
                'color' => 5,
                'informacion' => 'Rutinas de ejercicio y dieta',
                'created_at' => $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ],
            [
                'elemento_id' => $elementoIds[5],
                'nombre' => 'Proyectos',
                'color' => 6,
                'informacion' => 'Seguimiento de proyectos personales',
                'created_at' =>  $currentTimestamp,
                'updated_at' =>  $currentTimestamp
            ]
        ];

        // Insertar los calendarios
        DB::table('calendarios')->insert($calendarios);

        // Alternativa usando el modelo (activa eventos/observers)
        // foreach ($calendarios as $calendario) {
        //     Calendario::create($calendario);
        // }
    }
}