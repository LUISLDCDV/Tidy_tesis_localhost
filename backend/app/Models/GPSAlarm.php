<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GPSAlarm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gps_alarms';

    protected $fillable = [
        'user_id',
        'name',
        'latitude',
        'longitude',
        'radius',
        'type',
        'active',
        'notification_sound',
        'address',
        'trigger_count',
        'metadata'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'active' => 'boolean',
        'trigger_count' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los triggers de la alarma
     */
    public function triggers()
    {
        return $this->hasMany(GPSAlarmTrigger::class);
    }

    /**
     * Relación con los triggers recientes
     */
    public function recentTriggers()
    {
        return $this->hasMany(GPSAlarmTrigger::class)
            ->orderBy('triggered_at', 'desc')
            ->limit(10);
    }

    /**
     * Scope para alarmas activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para alarmas por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para alarmas cercanas a una ubicación
     */
    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = 5)
    {
        return $query->selectRaw(
            '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$latitude, $longitude, $latitude]
        )->having('distance', '<=', $radiusKm);
    }

    /**
     * Calcular distancia desde una ubicación específica
     */
    public function getDistanceFrom($latitude, $longitude)
    {
        $earthRadius = 6371; // Radio de la Tierra en kilómetros

        $latDiff = deg2rad($latitude - $this->latitude);
        $lonDiff = deg2rad($longitude - $this->longitude);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c * 1000; // Convertir a metros
    }

    /**
     * Verificar si una ubicación está dentro del radio de la alarma
     */
    public function isLocationWithinRadius($latitude, $longitude)
    {
        $distance = $this->getDistanceFrom($latitude, $longitude);
        return $distance <= $this->radius;
    }

    /**
     * Obtener el último trigger de esta alarma
     */
    public function getLastTriggerAttribute()
    {
        return $this->triggers()->latest('triggered_at')->first();
    }

    /**
     * Obtener estadísticas de activación
     */
    public function getTriggerStatsAttribute()
    {
        $triggers = $this->triggers();

        return [
            'total' => $triggers->count(),
            'this_month' => $triggers->where('triggered_at', '>=', now()->startOfMonth())->count(),
            'this_week' => $triggers->where('triggered_at', '>=', now()->startOfWeek())->count(),
            'enter_count' => $triggers->where('action', 'enter')->count(),
            'exit_count' => $triggers->where('action', 'exit')->count(),
            'last_triggered' => $this->last_trigger?->triggered_at
        ];
    }

    /**
     * Obtener configuración de notificación
     */
    public function getNotificationConfigAttribute()
    {
        $metadata = $this->metadata ?? [];

        return [
            'sound' => $this->notification_sound ?? 'default',
            'vibration' => $metadata['vibration'] ?? true,
            'push_enabled' => $metadata['push_enabled'] ?? true,
            'email_enabled' => $metadata['email_enabled'] ?? false
        ];
    }

    /**
     * Obtener información de ubicación formateada
     */
    public function getLocationInfoAttribute()
    {
        return [
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'address' => $this->address,
            'radius_meters' => $this->radius,
            'radius_formatted' => $this->radius >= 1000
                ? round($this->radius / 1000, 1) . ' km'
                : $this->radius . ' m'
        ];
    }

    /**
     * Generar URL de Google Maps
     */
    public function getGoogleMapsUrlAttribute()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Obtener icono según el tipo de alarma
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'enter' => 'login',
            'exit' => 'logout',
            'both' => 'swap_horiz',
            default => 'location_on'
        };
    }

    /**
     * Obtener color según el tipo de alarma
     */
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'enter' => 'green',
            'exit' => 'red',
            'both' => 'blue',
            default => 'grey'
        };
    }

    /**
     * Verificar si la alarma debe activarse para una ubicación
     */
    public function shouldTriggerForLocation($latitude, $longitude, $currentlyInside = null)
    {
        if (!$this->active) {
            return false;
        }

        $isInside = $this->isLocationWithinRadius($latitude, $longitude);

        // Si no sabemos el estado anterior, no activar
        if ($currentlyInside === null) {
            return false;
        }

        switch ($this->type) {
            case 'enter':
                return !$currentlyInside && $isInside;

            case 'exit':
                return $currentlyInside && !$isInside;

            case 'both':
                return $currentlyInside !== $isInside;

            default:
                return false;
        }
    }

    /**
     * Incrementar contador de activaciones
     */
    public function incrementTriggerCount()
    {
        $this->increment('trigger_count');
    }

    /**
     * Actualizar metadatos
     */
    public function updateMetadata($key, $value)
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Obtener valor de metadatos
     */
    public function getMetadataValue($key, $default = null)
    {
        return ($this->metadata ?? [])[$key] ?? $default;
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeSearchByName($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Scope para ordenar por distancia desde una ubicación
     */
    public function scopeOrderByDistance($query, $latitude, $longitude)
    {
        return $query->selectRaw(
            '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$latitude, $longitude, $latitude]
        )->orderBy('distance');
    }

    /**
     * Scope para filtrar por rango de fechas de creación
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Formatear para respuesta API
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'type' => $this->type,
            'type_icon' => $this->type_icon,
            'type_color' => $this->type_color,
            'active' => $this->active,
            'address' => $this->address,
            'notification_sound' => $this->notification_sound,
            'trigger_count' => $this->trigger_count,
            'location_info' => $this->location_info,
            'notification_config' => $this->notification_config,
            'google_maps_url' => $this->google_maps_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'trigger_stats' => $this->trigger_stats
        ];
    }
}