<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposNotasSeeder extends Seeder
{
    public function run()
    {
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
                'puntos_necesarios' => 0,
                'is_premium' => true
            ],
            [
                'nombre' => 'Dibujo Nota',
                'descripcion' => 'Tablero un dibujo',
                'puntos_necesarios' => 0,
                'is_premium' => true
            ],
            [
                'nombre' => 'Diagrama Nota',
                'descripcion' => 'Organizar ideas o procesos',
                'puntos_necesarios' => 0,
                'is_premium' => true
            ],     
        ]);
    }
}