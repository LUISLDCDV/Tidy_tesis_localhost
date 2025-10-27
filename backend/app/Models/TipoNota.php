<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNota extends Model
{
    use HasFactory;

    protected $table = 'tipos_notas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'puntos_necesarios',
        'is_premium'
    ];

    protected $casts = [
        'puntos_necesarios' => 'integer',
        'is_premium' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con las notas
     */
    public function notas()
    {
        return $this->hasMany(Elemento::class, 'tipo_nota_id');
    }

    /**
     * Obtener todos los tipos de nota disponibles
     */
    public static function getTiposDisponibles()
    {
        return static::orderBy('id')->get(['id', 'nombre', 'descripcion']);
    }

    /**
     * Verificar si un tipo de nota existe
     */
    public static function existe($id)
    {
        return static::where('id', $id)->exists();
    }

    /**
     * Obtener tipo de nota por ID con validación
     */
    public static function obtenerPorId($id)
    {
        return static::find($id);
    }

    /**
     * Seed de tipos de nota por defecto
     */
    public static function seedTiposDefault()
    {
        $tipos = [
            ['id' => 1, 'nombre' => 'Normal', 'descripcion' => 'Nota de texto simple', 'puntos_necesarios' => 0],
            ['id' => 2, 'nombre' => 'Comida semana', 'descripcion' => 'Planificador de comidas semanal', 'puntos_necesarios' => 0],
            ['id' => 3, 'nombre' => 'Claves', 'descripcion' => 'Nota con registro de claves', 'puntos_necesarios' => 0],
            ['id' => 4, 'nombre' => 'Código', 'descripcion' => 'Nota para fragmentos de código', 'puntos_necesarios' => 0],
            ['id' => 5, 'nombre' => 'Compras super', 'descripcion' => 'Lista de productos supermercado', 'puntos_necesarios' => 0],
            ['id' => 6, 'nombre' => 'Gastos mensuales', 'descripcion' => 'Registro de pagos pendientes', 'puntos_necesarios' => 0],
            ['id' => 7, 'nombre' => 'Juegos', 'descripcion' => 'Tabla de puntos juego', 'puntos_necesarios' => 0],
            ['id' => 8, 'nombre' => 'Presupuesto', 'descripcion' => 'Gestión de presupuesto', 'puntos_necesarios' => 0],
            ['id' => 9, 'nombre' => 'Timebox', 'descripcion' => 'Gestión de tiempo', 'puntos_necesarios' => 0],
            ['id' => 10, 'nombre' => 'Recetas', 'descripcion' => 'Registros recetas comidas', 'puntos_necesarios' => 0],
            ['id' => 11, 'nombre' => 'Medidas construcción', 'descripcion' => 'Guardar medidas de elemento', 'puntos_necesarios' => 0],
            ['id' => 12, 'nombre' => 'Gustos y recomendaciones', 'descripcion' => 'Guardar recomendaciones', 'puntos_necesarios' => 0],
            ['id' => 13, 'nombre' => 'Pedido comida grupa', 'descripcion' => 'Organizar pedidos comida grupal', 'puntos_necesarios' => 0],
            ['id' => 14, 'nombre' => 'Nota viaje', 'descripcion' => 'Organizar recorrido viaje', 'puntos_necesarios' => 0],
            ['id' => 15, 'nombre' => 'Dibujo Nota', 'descripcion' => 'Tablero un dibujo', 'puntos_necesarios' => 0],
            ['id' => 16, 'nombre' => 'Diagrama Nota', 'descripcion' => 'Organizar ideas o procesos', 'puntos_necesarios' => 0],
        ];

        foreach ($tipos as $tipo) {
            static::updateOrCreate(
                ['id' => $tipo['id']],
                $tipo
            );
        }
    }
}