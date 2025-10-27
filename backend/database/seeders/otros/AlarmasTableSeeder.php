<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlarmasTableSeeder extends Seeder
{
    public function run()
    {
        $currentTimestamp = Carbon::now();

        $alarmas = [
            [
                'elemento_id' => 4,
                'nombre' => 'Alarma 1',
                'fecha' => '2024-11-28',
                'hora' => '08:00:00',
                'fechaVencimiento' => '2024-12-01',
                'horaVencimiento' => '12:00:00',
                'informacion' => 'Información adicional para la alarma 1.',
                'intensidad_volumen' => 50,
                'gps' => json_encode(['lat' => '19.432608', 'lng' => '-99.133209']),
                'distancia_radio' => json_encode(['radius' => 50]),
                'configuraciones' => json_encode(['repeat' => true]),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 5,
                'nombre' => 'Alarma 2',
                'fecha' => '2024-11-29',
                'hora' => '09:30:00',
                'fechaVencimiento' => '2024-12-02',
                'horaVencimiento' => '14:30:00',
                'informacion' => 'Información adicional para la alarma 2.',
                'intensidad_volumen' => 80,
                'gps' => json_encode(['lat' => '20.659698', 'lng' => '-103.349609']),
                'distancia_radio' => json_encode(['radius' => 100]),
                'configuraciones' => json_encode(['repeat' => false]),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 6,
                'nombre' => 'Alarma 3',
                'fecha' => '2024-11-30',
                'hora' => '11:00:00',
                'fechaVencimiento' => '2024-12-03',
                'horaVencimiento' => '16:00:00',
                'informacion' => 'Información adicional para la alarma 3.',
                'intensidad_volumen' => 31,
                'gps' => json_encode(['lat' => '21.161907', 'lng' => '-86.851524']),
                'distancia_radio' => json_encode(['radius' => 150]),
                'configuraciones' => json_encode(['repeat' => true]),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ];

        DB::table('alarmas')->insert($alarmas);
    }

}

