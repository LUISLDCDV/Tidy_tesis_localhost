<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UsuarioAdmin;
use App\Models\UsuarioCuenta;
use App\Models\Permiso;
use App\Models\UserLevel;
use App\Models\Notification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, SoftDeletes, HasRoles, HasFactory;

    protected $table = 'users'; // Tabla asociada
    protected $primaryKey = 'id'; // Llave primaria

    protected $fillable = [
        'name', 'last_name', 'email', 'phone', 'password',
        'firebase_uid', 'firebase_token', 'photo', 'provider', 'last_login_at'
    ];

    protected $hidden = [
        'password', 'remember_token', 'firebase_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $dates = ['deleted_at']; // Columna de fechas para SoftDeletes

    /**
     * Relación con permisos (muchos a muchos).
     */
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'user_permiso');
    }

    /**
     * Relación con notificaciones (uno a muchos).
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    /**
     * Relación con UsuarioCuenta (uno a uno).
     */
    public function usuarioCuenta()
    {
        return $this->hasOne(UsuarioCuenta::class);
    }

    /**
     * Alias de usuarioCuenta para facilitar el uso
     */
    public function cuenta()
    {
        return $this->hasOne(UsuarioCuenta::class);
    }

    /**
     * Relación con UsuarioAdmin (uno a uno).
     */
    public function usuarioAdmin()
    {
        return $this->hasOne(UsuarioAdmin::class);
    }

    /**
     * Relación con comentarios del usuario
     */
    public function userComments()
    {
        return $this->hasMany(\App\Models\UserComment::class);
    }

    /**
     * Relación con comentarios respondidos como admin
     */
    public function respondedComments()
    {
        return $this->hasMany(\App\Models\UserComment::class, 'responded_by');
    }

    /**
     * Relación con UserLevel (uno a uno).
     */
    public function userLevel()
    {
        return $this->hasOne(UserLevel::class);
    }

    /**
     * Relación con notificaciones (uno a muchos) - usando el modelo Notification correcto
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
