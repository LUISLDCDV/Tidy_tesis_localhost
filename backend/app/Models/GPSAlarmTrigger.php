<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GPSAlarmTrigger extends Model
{
    use HasFactory;

    protected $table = 'gps_alarm_triggers';

    protected $fillable = [
        'gps_alarm_id',
        'user_id',
        'action',
        'latitude',
        'longitude',
        'accuracy',
        'triggered_at',
        'metadata'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'triggered_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'triggered_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Relación con la alarma GPS
     */
    public function gpsAlarm()
    {
        return $this->belongsTo(GPSAlarm::class);
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para triggers por acción
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para triggers recientes
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('triggered_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope para triggers de hoy
     */
    public function scopeToday($query)
    {
        return $query->whereDate('triggered_at', today());
    }

    /**
     * Scope para triggers de esta semana
     */
    public function scopeThisWeek($query)
    {
        return $query->where('triggered_at', '>=', now()->startOfWeek());
    }

    /**
     * Scope para triggers de este mes
     */
    public function scopeThisMonth($query)
    {
        return $query->where('triggered_at', '>=', now()->startOfMonth());
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
            'accuracy' => $this->accuracy,
            'accuracy_formatted' => $this->accuracy ? round($this->accuracy) . 'm' : null
        ];
    }

    /**
     * Generar URL de Google Maps para la ubicación del trigger
     */
    public function getGoogleMapsUrlAttribute()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Obtener el tiempo transcurrido desde el trigger
     */
    public function getTimeAgoAttribute()
    {
        return $this->triggered_at->diffForHumans();
    }

    /**
     * Obtener información del dispositivo desde metadatos
     */
    public function getDeviceInfoAttribute()
    {
        $metadata = $this->metadata ?? [];

        return [
            'user_agent' => $metadata['device_info'] ?? null,
            'battery_level' => $metadata['battery_level'] ?? null,
            'network_type' => $metadata['network_type'] ?? null,
            'trigger_source' => $metadata['trigger_source'] ?? 'unknown'
        ];
    }

    /**
     * Verificar si el trigger es de entrada
     */
    public function isEnterAction()
    {
        return $this->action === 'enter';
    }

    /**
     * Verificar si el trigger es de salida
     */
    public function isExitAction()
    {
        return $this->action === 'exit';
    }

    /**
     * Obtener el icono según la acción
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'enter' => 'login',
            'exit' => 'logout',
            default => 'location_on'
        };
    }

    /**
     * Obtener el color según la acción
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'enter' => 'green',
            'exit' => 'red',
            default => 'grey'
        };
    }

    /**
     * Obtener texto descriptivo de la acción
     */
    public function getActionTextAttribute()
    {
        return match($this->action) {
            'enter' => 'Entrada',
            'exit' => 'Salida',
            default => 'Desconocido'
        };
    }

    /**
     * Calcular distancia desde la ubicación de la alarma
     */
    public function getDistanceFromAlarmAttribute()
    {
        if (!$this->gpsAlarm) {
            return null;
        }

        return $this->gpsAlarm->getDistanceFrom($this->latitude, $this->longitude);
    }

    /**
     * Verificar si el trigger está dentro del radio de la alarma
     */
    public function isWithinAlarmRadius()
    {
        if (!$this->gpsAlarm) {
            return false;
        }

        return $this->gpsAlarm->isLocationWithinRadius($this->latitude, $this->longitude);
    }

    /**
     * Obtener la duración desde el trigger anterior del mismo tipo
     */
    public function getDurationFromPreviousTrigger()
    {
        $previousTrigger = static::where('gps_alarm_id', $this->gps_alarm_id)
            ->where('action', $this->action === 'enter' ? 'exit' : 'enter')
            ->where('triggered_at', '<', $this->triggered_at)
            ->orderBy('triggered_at', 'desc')
            ->first();

        if (!$previousTrigger) {
            return null;
        }

        return $this->triggered_at->diffInMinutes($previousTrigger->triggered_at);
    }

    /**
     * Obtener valor de metadatos
     */
    public function getMetadataValue($key, $default = null)
    {
        return ($this->metadata ?? [])[$key] ?? $default;
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
     * Scope para buscar por rango de fechas
     */
    public function scopeTriggeredBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('triggered_at', [$startDate, $endDate]);
    }

    /**
     * Scope para triggers con precisión alta
     */
    public function scopeHighAccuracy($query, $maxAccuracy = 10)
    {
        return $query->where('accuracy', '<=', $maxAccuracy);
    }

    /**
     * Scope para triggers ordenados por tiempo
     */
    public function scopeOrderByTime($query, $direction = 'desc')
    {
        return $query->orderBy('triggered_at', $direction);
    }

    /**
     * Formatear para respuesta API
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'gps_alarm_id' => $this->gps_alarm_id,
            'alarm_name' => $this->gpsAlarm?->name,
            'action' => $this->action,
            'action_text' => $this->action_text,
            'action_icon' => $this->action_icon,
            'action_color' => $this->action_color,
            'location_info' => $this->location_info,
            'triggered_at' => $this->triggered_at,
            'time_ago' => $this->time_ago,
            'device_info' => $this->device_info,
            'distance_from_alarm' => $this->distance_from_alarm,
            'within_radius' => $this->isWithinAlarmRadius(),
            'google_maps_url' => $this->google_maps_url,
            'created_at' => $this->created_at
        ];
    }

    /**
     * Obtener estadísticas de triggers agrupadas
     */
    public static function getGroupedStats($userId, $groupBy = 'day', $days = 30)
    {
        $query = static::where('user_id', $userId)
            ->where('triggered_at', '>=', now()->subDays($days));

        switch ($groupBy) {
            case 'hour':
                return $query->selectRaw('DATE_FORMAT(triggered_at, "%Y-%m-%d %H:00:00") as period, COUNT(*) as count, action')
                    ->groupBy('period', 'action')
                    ->orderBy('period')
                    ->get();

            case 'day':
                return $query->selectRaw('DATE(triggered_at) as period, COUNT(*) as count, action')
                    ->groupBy('period', 'action')
                    ->orderBy('period')
                    ->get();

            case 'week':
                return $query->selectRaw('YEARWEEK(triggered_at) as period, COUNT(*) as count, action')
                    ->groupBy('period', 'action')
                    ->orderBy('period')
                    ->get();

            default:
                return collect();
        }
    }
}