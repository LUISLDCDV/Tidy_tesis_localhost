<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Permiso extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'permisos';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_permiso');
    }
}
