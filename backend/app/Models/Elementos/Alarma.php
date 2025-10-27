<?php

namespace App\Models\Elementos;

// namespace App\Models;
use  App\Models\Elementos\Elemento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alarma extends Model
{
    use HasFactory;

    protected $table = 'alarmas';
    protected $primaryKey = 'id';
    //FALTA GPS 

    protected $fillable = [
        'fecha',
        'hora',
        'fechaVencimiento',
        'horaVencimiento',
        'nombre',
        'informacion',
        'para_no_olvidar',
        'intensidad_volumen',
        'configuraciones',
    ];
    
    public function elemento()
    {
        return $this->belongsTo(Elemento::class);
    }

    protected $casts = [
        'configuraciones' => 'array',
    ];

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
