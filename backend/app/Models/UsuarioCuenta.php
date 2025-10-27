<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  App\Models\Elementos\Elemento;
// use  App\Models\Elementos\Elemento;

class UsuarioCuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'id_medio_pago',
        'puntaje', // Referencia al puntaje
        'configuraciones',
        'is_premium',
        'premium_expires_at',
        'mercadopago_subscription_id',
        'total_xp',
        'current_level',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medioPago()
    {
        return $this->hasOne(MedioDePago::class, 'id_medio_pago');
    }

    public function puntaje()
    {
        return $this->hasOne(Puntaje::class, 'id_usuario_cuenta');
    }

    public function elementos()
    {
        return $this->hasMany(Elemento::class, 'cuenta_id');
    }

    protected $casts = [
        'is_premium' => 'boolean',
        'premium_expires_at' => 'datetime',
    ];

    public function isPremiumActive()
    {
        return $this->is_premium &&
               $this->premium_expires_at &&
               $this->premium_expires_at->isFuture();
    }

    



}