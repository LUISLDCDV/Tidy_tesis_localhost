<?php

// app/Models/Evento.php
// namespace App\Models;

namespace App\Models\Elementos;

use  App\Models\Elementos\Elemento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eventos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'elemento_id',
        'nombre',
        'informacion',
        'fechaVencimiento',
        'horaVencimiento',
        'calendario_id',
        'metadata',
        'tipo',
        'gps',
        'clima',
        'recurrencia'
    ];

    protected $casts = [
        'metadata' => 'array',
        'gps' => 'array',
        'clima' => 'array',
        'recurrencia' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['fecha_inicio', 'fecha_fin'];

    // Accessors para compatibilidad con API
    public function getFechaInicioAttribute()
    {
        if ($this->fechaVencimiento && $this->horaVencimiento) {
            return $this->fechaVencimiento . ' ' . $this->horaVencimiento;
        }
        return $this->fechaVencimiento;
    }

    public function getFechaFinAttribute()
    {
        // Por defecto, fecha_fin es igual a fecha_inicio
        // Esto puede ser personalizado si tienes un campo separado
        if ($this->fechaVencimiento && $this->horaVencimiento) {
            return $this->fechaVencimiento . ' ' . $this->horaVencimiento;
        }
        return $this->fechaVencimiento;
    }

    // Mutators para aceptar fecha_inicio al crear/actualizar
    public function setFechaInicioAttribute($value)
    {
        if ($value) {
            $datetime = \Carbon\Carbon::parse($value);
            $this->attributes['fechaVencimiento'] = $datetime->toDateString();
            $this->attributes['horaVencimiento'] = $datetime->toTimeString();
        }
    }

    public function setFechaFinAttribute($value)
    {
        // Puedes manejar esto si necesitas almacenar fecha_fin por separado
        // Por ahora, no hacemos nada ya que usamos la misma fecha de inicio
    }

    // Relación con Calendario
    public function calendario()
    {
        return $this->belongsTo(Calendario::class, 'calendario_id');
    }

    // Relación con Elemento
    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }
}
