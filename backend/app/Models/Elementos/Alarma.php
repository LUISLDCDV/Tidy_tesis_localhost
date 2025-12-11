<?php

namespace App\Models\Elementos;

// namespace App\Models;
use  App\Models\Elementos\Elemento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alarma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alarmas';
    protected $primaryKey = 'id';
    //FALTA GPS 

    protected $fillable = [
        'elemento_id',
        'fecha',
        'hora',
        'fechaVencimiento',
        'horaVencimiento',
        'nombre',
        'informacion',
        'intensidad_volumen',
        'configuraciones',
        'tipo_alarma',
        'ubicacion',
    ];

    public function elemento()
    {
        return $this->belongsTo(Elemento::class);
    }

    protected $casts = [
        'configuraciones' => 'array',
        'ubicacion' => 'array',
    ];

    protected $appends = ['descripcion'];

    // Accessor para compatibilidad con API
    public function getDescripcionAttribute()
    {
        return $this->attributes['informacion'] ?? '';
    }

    public function getGpsAttribute()
    {
        return $this->configuraciones['gps'] ?? null;
    }

    public function getClimaAttribute()
    {
        return $this->configuraciones['clima'] ?? null;
    }

    public function setGpsAttribute($value)
    {
        $configuraciones = $this->configuraciones ?? [];
        $configuraciones['gps'] = $value;
        $this->attributes['configuraciones'] = json_encode($configuraciones);
    }

    public function setClimaAttribute($value)
    {
        $configuraciones = $this->configuraciones ?? [];
        $configuraciones['clima'] = $value;
        $this->attributes['configuraciones'] = json_encode($configuraciones);
    }
}
