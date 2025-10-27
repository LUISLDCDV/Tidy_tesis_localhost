<?php


namespace App\Models\Elementos;

use  App\Models\Elementos\Elemento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

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
