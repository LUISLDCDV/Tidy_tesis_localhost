<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDeviceToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'device_token',
        'device_type',
        'device_name',
        'is_active',
        'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    /**
     * Relación con el modelo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para tokens activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para un tipo específico de dispositivo
     */
    public function scopeDeviceType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Obtener tokens activos de un usuario
     */
    public static function getActiveTokensForUser($userId)
    {
        return static::where('user_id', $userId)
            ->active()
            ->pluck('device_token')
            ->toArray();
    }

    /**
     * Marcar token como utilizado recientemente
     */
    public function markAsUsed()
    {
        $this->update([
            'last_used_at' => now(),
            'is_active' => true
        ]);
    }

    /**
     * Limpiar tokens antiguos e inactivos
     */
    public static function cleanupOldTokens($daysOld = 30)
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return static::where('last_used_at', '<', $cutoffDate)
            ->orWhere('is_active', false)
            ->delete();
    }
}