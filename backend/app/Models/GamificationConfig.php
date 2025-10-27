<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GamificationConfig extends Model
{
    use HasFactory;

    protected $table = 'gamification_config';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Obtener valor de XP por clave con caché
     */
    public static function getXP($key, $default = 5)
    {
        return Cache::remember("xp_{$key}", 3600, function () use ($key, $default) {
            $config = self::where('key', $key)->first();
            return $config ? $config->value : $default;
        });
    }

    /**
     * Limpiar caché al actualizar
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::flush();
        });

        static::deleted(function () {
            Cache::flush();
        });
    }
}
