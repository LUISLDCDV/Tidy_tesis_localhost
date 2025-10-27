<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedioDePago extends Model
{
    use HasFactory;

    protected $table = 'medios_de_pago';
    protected $primaryKey = 'id_medio_pago';

    protected $fillable = [
        'nombre',
        'tipo',
        'tipo_subscripcion',
        'identificador',
        'fecha_expiracion',
    ];

    public function usuarioCuenta()
    {
        return $this->belongsTo(UsuarioCuenta::class);
    }
}
