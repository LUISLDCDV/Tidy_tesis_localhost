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
        'clima'

    ];

    protected $casts = [
        'metadata' => 'array',
        'gps' => 'array',
        'clima' => 'array',
        'deleted_at' => 'datetime',
    ];

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
