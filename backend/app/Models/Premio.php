<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Premio extends Model
{
    use HasFactory;

    protected $table = 'premios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'puntos_necesarios',
    ];

    public function puntaje()
    {
        return $this->belongsTo(Puntaje::class, 'id_puntaje');
    }
}
