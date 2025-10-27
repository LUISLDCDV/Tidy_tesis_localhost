<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'type',
        'condition_type',
        'condition_value',
        'experience_reward',
        'is_active'
    ];

    protected $casts = [
        'condition_value' => 'integer',
        'experience_reward' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Usuarios que han obtenido este logro
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Usuarios que han desbloqueado este logro
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    /**
     * Crear logros predeterminados del sistema
     */
    public static function createDefaultAchievements()
    {
        $achievements = [
            // Logro de bienvenida y verificación
            [
                'name' => 'Verificado',
                'description' => 'Verifica tu dirección de email',
                'icon' => '✅',
                'type' => 'email_verified',
                'condition_type' => 'verified',
                'condition_value' => 1,
                'experience_reward' => 100,
                'is_active' => true
            ],

            // Logros de elementos creados
            [
                'name' => 'Primer Paso',
                'description' => 'Crea tu primer elemento en Tidy',
                'icon' => '🎯',
                'type' => 'element_created',
                'condition_type' => 'count',
                'condition_value' => 1,
                'experience_reward' => 50,
                'is_active' => true
            ],
            [
                'name' => 'Productivo',
                'description' => 'Crea 10 elementos',
                'icon' => '📝',
                'type' => 'element_created',
                'condition_type' => 'count',
                'condition_value' => 10,
                'experience_reward' => 200,
                'is_active' => true
            ],
            [
                'name' => 'Organizador Experto',
                'description' => 'Crea 50 elementos',
                'icon' => '🏆',
                'type' => 'element_created',
                'condition_type' => 'count',
                'condition_value' => 50,
                'experience_reward' => 500,
                'is_active' => true
            ],

            // Logros de objetivos completados
            [
                'name' => 'Alcanzador',
                'description' => 'Completa tu primer objetivo',
                'icon' => '🎯',
                'type' => 'goal_completed',
                'condition_type' => 'count',
                'condition_value' => 1,
                'experience_reward' => 100,
                'is_active' => true
            ],
            [
                'name' => 'Determinado',
                'description' => 'Completa 5 objetivos',
                'icon' => '🏅',
                'type' => 'goal_completed',
                'condition_type' => 'count',
                'condition_value' => 5,
                'experience_reward' => 300,
                'is_active' => true
            ],
            [
                'name' => 'Planificador Maestro',
                'description' => 'Completa un objetivo con más de 5 metas',
                'icon' => '🎯',
                'type' => 'goal_with_many_metas',
                'condition_type' => 'metas_count',
                'condition_value' => 5,
                'experience_reward' => 500,
                'is_active' => true
            ],

            // Logros de niveles
            [
                'name' => 'Novato',
                'description' => 'Alcanza el nivel 5',
                'icon' => '⭐',
                'type' => 'level_reached',
                'condition_type' => 'level',
                'condition_value' => 5,
                'experience_reward' => 150,
                'is_active' => true
            ],
            [
                'name' => 'Experimentado',
                'description' => 'Alcanza el nivel 10',
                'icon' => '🌟',
                'type' => 'level_reached',
                'condition_type' => 'level',
                'condition_value' => 10,
                'experience_reward' => 300,
                'is_active' => true
            ],
            [
                'name' => 'Maestro de la Productividad',
                'description' => 'Alcanza el nivel 25',
                'icon' => '👑',
                'type' => 'level_reached',
                'condition_type' => 'level',
                'condition_value' => 25,
                'experience_reward' => 1000,
                'is_active' => true
            ],

            // Logros de uso diario
            [
                'name' => 'Consistente',
                'description' => 'Usa la app 7 días seguidos',
                'icon' => '📅',
                'type' => 'daily_streak',
                'condition_type' => 'days',
                'condition_value' => 7,
                'experience_reward' => 250,
                'is_active' => true
            ],
            [
                'name' => 'Dedicado',
                'description' => 'Usa la app 30 días seguidos',
                'icon' => '🔥',
                'type' => 'daily_streak',
                'condition_type' => 'days',
                'condition_value' => 30,
                'experience_reward' => 750,
                'is_active' => true
            ],

            // Logros especiales
            [
                'name' => 'Madrugador',
                'description' => 'Crea un elemento antes de las 6 AM',
                'icon' => '🌅',
                'type' => 'special',
                'condition_type' => 'time_based',
                'condition_value' => 6,
                'experience_reward' => 100,
                'is_active' => true
            ],
            [
                'name' => 'Noctámbulo',
                'description' => 'Crea un elemento después de las 11 PM',
                'icon' => '🌙',
                'type' => 'special',
                'condition_type' => 'time_based',
                'condition_value' => 23,
                'experience_reward' => 100,
                'is_active' => true
            ]
        ];

        foreach ($achievements as $achievement) {
            self::firstOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }

    /**
     * Scope para logros activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para un tipo específico de logro
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}