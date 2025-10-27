<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puntaje extends Model
{
    use HasFactory;

    protected $table = 'puntajes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cantidad',
        'rango',
    ];

    public function usuarioCuenta()
    {
        return $this->belongsTo(UsuarioCuenta::class, 'id_usuario_cuenta');
    }

    public function premios()
    {
        return $this->hasMany(Premio::class, 'id_puntaje');
    }
}
