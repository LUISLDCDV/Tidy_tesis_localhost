<?php


namespace App\Models\Elementos;

Use App\Models\User;
Use App\Models\Elementos\Alarma;
Use App\Models\Elementos\Objetivo;
Use App\Models\Elementos\Nota;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Elemento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'elementos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tipo',
        'descripcion',
        'estado',
        'imagen',
        'orden',
        'configuracion',
        'cuenta_id',
    ];

    protected $casts = [
        'configuracion' => 'array',
    ];

    // Ocultar relaciones sensibles en JSON para seguridad
    protected $hidden = [
        'usuario'
    ];

    
    public function cuenta()
    {
        return $this->belongsTo(\App\Models\UsuarioCuenta::class, 'cuenta_id');
    }

    public function usuario()
    {
        return $this->hasOneThrough(
            User::class,
            \App\Models\UsuarioCuenta::class,
            'id', // Foreign key on UsuarioCuenta table (cuenta_id en elementos)
            'id', // Foreign key on User table
            'cuenta_id', // Local key on Elemento table
            'user_id' // Local key on UsuarioCuenta table
        );
    }
    
    // Método general para actualizar el contenido de cualquier tipo de elemento
    public function actualizarElemento($nuevoContenido)
    {
        $this->contenido = $nuevoContenido;
        $this->save();
    }



    public function alarma()
    {
        return $this->hasOne(Alarma::class);
    }

    public function objetivo()
    {
        return $this->hasOne(Objetivo::class);
    }

    public function nota()
    {
        return $this->hasOne(Nota::class);
    }
    
}
