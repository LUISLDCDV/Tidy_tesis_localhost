<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class ElementosTableSeeder extends Seeder
{
    public function run()
    {
        $currentTimestamp = Carbon::now();

        DB::table('elementos')->insert([
            

            //***NOTAS 1-3
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 1',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 2',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 3',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],



            //***ALARMA 4-6
            [
                'tipo' => 'alarma',
                'estado' => 'activo',
                'imagen' => 'recordatorio_icon.png',
                'descripcion' => 'alarma 1',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'alarma',
                'estado' => 'activo',
                'imagen' => 'recordatorio_icon.png',
                'descripcion' => 'alarma 2',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'alarma',
                'estado' => 'activo',
                'imagen' => 'recordatorio_icon.png',
                'descripcion' => 'alarma 3',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],


            //***CALENDARIOS 6 -11
            [
                'tipo' => 'calendario',
                'estado' => 'activo',
                'imagen' => 'calendario_icon.png',
                'descripcion' => 'calendario 1',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'calendario',
                'estado' => 'activo',
                'imagen' => 'calendario_icon.png',
                'descripcion' => 'calendario 2',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],

            [
                'tipo' => 'calendario',
                'estado' => 'activo',
                'imagen' => 'calendario_icon.png',
                'descripcion' => 'calendario 3',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],

            [
                'tipo' => 'calendario',
                'estado' => 'activo',
                'imagen' => 'calendario_icon.png',
                'descripcion' => 'calendario 4',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],

            [
                'tipo' => 'calendario',
                'estado' => 'activo',
                'imagen' => 'calendario_icon.png',
                'descripcion' => 'calendario 5',
                'cuenta_id' => 1,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],


            //***EVENTO 12-17

            [
                'tipo' => 'evento',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'evento 1',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'evento',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'evento 2',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'evento',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'evento 3',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'evento',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'evento 4',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'evento',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'evento 5',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],

   
            //***OBJETIVOS 18 - 20
            [
                'tipo' => 'objetivo',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'objetivo 1',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'objetivo',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'objetivo 2',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'objetivo',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'objetivo 3',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            
            
            //***METAS 21 -26
            [
                'tipo' => 'meta',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'meta 1',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'meta',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'meta 2',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'meta',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'meta 3',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'meta',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'meta 4',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'meta',
                'estado' => 'inactivo',
                'imagen' => null,
                'cuenta_id' => 1,
                'descripcion' => 'meta 5',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],           
            //27-39
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 25',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 26',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 27',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 28',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 29',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 30',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 31',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 32',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 33',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 34',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 35',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 36',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'tipo' => 'nota',
                'estado' => 'activo',
                'imagen' => 'nota_icon.png',
                'cuenta_id' => 1,
                'descripcion' => 'nota 37',
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
        ]);
    }
}
