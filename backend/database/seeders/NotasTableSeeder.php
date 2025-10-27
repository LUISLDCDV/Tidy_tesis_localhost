<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotasTableSeeder extends Seeder
{
    public function run()
    {
        $currentTimestamp = Carbon::now();

        DB::table('tipos_notas')->insert([
            [
                'nombre' => 'Normal',
                'descripcion' => 'Nota de texto simple',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Comida semana',
                'descripcion' => 'Planificador de comidas semanal',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Claves',
                'descripcion' => 'Nota con con registro de claves',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Código',
                'descripcion' => 'Nota para fragmentos de código',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Compras super',
                'descripcion' => 'Lista de productos supermercado',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Gastos mensuales',
                'descripcion' => 'Registro de pagos pendientes',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Juegos',
                'descripcion' => 'Tabla de puntos juego',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Presupuesto',
                'descripcion' => 'Gestión de presupuesto',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Timebox',
                'descripcion' => 'Gestión de tiempo',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Recetas',
                'descripcion' => 'Registros recetas comidas',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Medidas construcción',
                'descripcion' => 'Guardar medidas de elemento',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Gustos y recomendaciones',
                'descripcion' => 'Guardar recomendaciones',
                'puntos_necesarios' => 0
            ],
            [
                'nombre' => 'Pedido comida grupa',
                'descripcion' => 'Organizar pedidos comida grupal',
                'puntos_necesarios' => 0
            ],      
            [
                'nombre' => 'Nota viaje',
                'descripcion' => 'Organizar recorrido viaje',
                'puntos_necesarios' => 0
            ],     
            [
                'nombre' => 'Dibujo Nota',
                'descripcion' => 'Tablero un dibujo',
                'puntos_necesarios' => 0
            ],     
            [
                'nombre' => 'Diagrama Nota',
                'descripcion' => 'Organizar ideas o procesos',
                'puntos_necesarios' => 0
            ],     
        ]);



        DB::table('notas')->insert([
            [
                'elemento_id' => 1, // ID de un elemento existente
                'fecha' => Carbon::now()->toDateString(),
                'nombre' => 'Primera Nota',
                'tipo_nota_id' => 1,
                'informacion' => 'Esta es la primera nota.',
                'contenido' => json_encode(['contenido' => 'Detalles de la nota']),
                'clave' => bcrypt('clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 2, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Segunda Nota',
                'tipo_nota_id' => 1,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 3, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Tercera Nota',
                'tipo_nota_id' => 1,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],

            //NUEVAS NOTAS
            [
                'elemento_id' => 25, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Comida semana Nota',
                'tipo_nota_id' => 2,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 26, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Claves Nota',
                'tipo_nota_id' => 3,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 27, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'fragmento de código Nota',
                'tipo_nota_id' => 4,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 28, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Compras super Nota',
                'tipo_nota_id' => 5,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 29, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Gastos mensuales Nota',
                'tipo_nota_id' => 6,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 30, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Juegos Nota',
                'tipo_nota_id' => 7,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 31, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Presupuesto Nota',
                'tipo_nota_id' => 8,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 32, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Gestión de tiempo Nota',
                'tipo_nota_id' => 9,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],


            

            //NUEVAS NOTAS
            [
                'elemento_id' => 33, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Recetas Nota',
                'tipo_nota_id' => 10,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 34, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Medidas construcción Nota',
                'tipo_nota_id' => 11,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 35, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Gustos y recomendaciones Nota',
                'tipo_nota_id' => 12,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],
            [
                'elemento_id' => 36, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Pedido comida grupa Nota',
                'tipo_nota_id' => 13,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],      
            [
                'elemento_id' => 37, // ID de otro elemento existente
                'fecha' => Carbon::now()->subDay()->toDateString(),
                'nombre' => 'Nota viaje',
                'tipo_nota_id' => 14,
                'informacion' => null,
                'contenido' => json_encode(['contenido' => 'Detalles adicionales']),
                'clave' => bcrypt('otra_clave_secreta'),
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ],

        ]);
    }
}

