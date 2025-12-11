<?php


namespace App\Models\Elementos;
// namespace App\Models;
use  App\Models\Elementos\Elemento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calendarios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'elemento_id',
        'nombre',
        'color',
        'informacion',
        'descripcion',
    ];

    protected $appends = ['descripcion'];

    // Accessor para compatibilidad con API
    public function getDescripcionAttribute()
    {
        return $this->attributes['informacion'] ?? '';
    }

    // Mutator para aceptar descripcion al crear/actualizar
    public function setDescripcionAttribute($value)
    {
        $this->attributes['informacion'] = $value;
    }

    // Relación con Elemento
    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    // Relación con Eventos
    public function eventos()
    {
        return $this->hasMany(Evento::class, 'calendario_id');
    }
}
