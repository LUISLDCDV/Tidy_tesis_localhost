<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAdmin extends Model
{
    use HasFactory;

    protected $table = 'usuario_admins';

    protected $fillable = [
        'id_usuario',
        'rol_id',
        'rol_nombre',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
