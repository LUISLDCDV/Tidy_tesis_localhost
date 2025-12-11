<?php


namespace App\Models\Elementos;

use  App\Models\Elementos\Elemento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nota extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'elemento_id',
        'tipo_nota_id',
        'fecha',
        'nombre',
        'informacion',
        'contenido',
        'clave',
    ];

    protected $attributes = [
        'tipo_nota_id' => 1, // Valor por defecto
    ];

    // Relación con Elemento
    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    // Si necesitas hacer cast a JSON en el modelo
    protected $casts = [
        'contenido' => 'array',
    ];
}
