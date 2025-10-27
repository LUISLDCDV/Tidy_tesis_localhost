<?php

namespace Database\Seeders;

use App\Models\Elementos\Evento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventosTableSeeder extends Seeder
{
    public function run()
    {

        $calendarioIds = range(1, 5);
        
        $elementoIds = range(12, 17);

        $eventos = [
            // Eventos personales
            [
                'calendario_id' => $calendarioIds[0], // Calendario Personal
                'elemento_id' => $elementoIds[0],
                'tipo' => 'recordatorio',
                'fechaVencimiento' => Carbon::tomorrow()->toDateString(),
                'horaVencimiento' => '09:00:00',
                'nombre' => 'Cita médica',
                'informacion' => 'Revisión anual con el cardiólogo',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'calendario_id' => $calendarioIds[0],
                'elemento_id' => $elementoIds[0],
                'tipo' => 'cumpleaños',
                'fechaVencimiento' => Carbon::now()->addDays(5)->toDateString(),
                'horaVencimiento' => '00:00:00',
                'nombre' => 'Cumpleaños de María',
                'informacion' => 'Comprar regalo y tarjeta',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            // Eventos de trabajo
            [
                'calendario_id' => $calendarioIds[1], // Calendario Trabajo
                'elemento_id' => $elementoIds[1],
                'tipo' => 'reunion',
                'fechaVencimiento' => Carbon::now()->next('Monday')->toDateString(),
                'horaVencimiento' => '14:30:00',
                'nombre' => 'Revisión de proyecto',
                'informacion' => 'Presentar avances al equipo directivo',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'calendario_id' => $calendarioIds[1],
                'elemento_id' => $elementoIds[1],
                'tipo' => 'entrega',
                'fechaVencimiento' => Carbon::now()->addWeeks(2)->toDateString(),
                'horaVencimiento' => '18:00:00',
                'nombre' => 'Entrega informe trimestral',
                'informacion' => 'Enviar documento completo a gerencia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            // Eventos familiares
            [
                'calendario_id' => $calendarioIds[2], // Calendario Familiar
                'elemento_id' => $elementoIds[2],
                'tipo' => 'familiar',
                'fechaVencimiento' => Carbon::now()->addDays(10)->toDateString(),
                'horaVencimiento' => '20:00:00',
                'nombre' => 'Cena familiar',
                'informacion' => 'En casa de los abuelos',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            // Eventos académicos
            [
                'calendario_id' => $calendarioIds[3], // Calendario Estudios
                'elemento_id' => $elementoIds[3],
                'tipo' => 'examen',
                'fechaVencimiento' => Carbon::now()->addDays(7)->toDateString(),
                'horaVencimiento' => '16:00:00',
                'nombre' => 'Examen final de Laravel',
                'informacion' => 'Llevar identificación y calculadora',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            // Eventos de fitness
            [
                'calendario_id' => $calendarioIds[4], // Calendario Fitness
                'elemento_id' => $elementoIds[4],
                'tipo' => 'ejercicio',
                'fechaVencimiento' => Carbon::now()->addDays(3)->toDateString(),
                'horaVencimiento' => '07:00:00',
                'nombre' => 'Clase de spinning',
                'informacion' => 'Llevar toalla y agua',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // Insertar los eventos
        DB::table('eventos')->insert($eventos);

        // Alternativa usando el modelo (mejor para triggers/observers)
        // foreach ($eventos as $evento) {
        //     Evento::create($evento);
        // }
    }
}