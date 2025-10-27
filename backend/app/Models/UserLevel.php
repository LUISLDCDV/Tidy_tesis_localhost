<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level',
        'current_experience',
        'total_experience',
        'experience_to_next_level'
    ];

    protected $casts = [
        'level' => 'integer',
        'current_experience' => 'integer',
        'total_experience' => 'integer',
        'experience_to_next_level' => 'integer'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcular experiencia necesaria para el siguiente nivel
     */
    public static function calculateExperienceForLevel($level)
    {
        // Fórmula progresiva: cada nivel requiere más experiencia
        return 100 * $level + ($level * 50);
    }

    /**
     * Verificar si el usuario puede subir de nivel
     */
    public function canLevelUp()
    {
        return $this->current_experience >= $this->experience_to_next_level;
    }

    /**
     * Subir de nivel
     */
    public function levelUp()
    {
        if (!$this->canLevelUp()) {
            return false;
        }

        $experienceOverflow = $this->current_experience - $this->experience_to_next_level;
        $newLevel = $this->level + 1;
        $newExperienceRequired = self::calculateExperienceForLevel($newLevel);

        $this->update([
            'level' => $newLevel,
            'current_experience' => $experienceOverflow,
            'experience_to_next_level' => $newExperienceRequired
        ]);

        // Verificar si puede subir otro nivel más
        if ($this->canLevelUp()) {
            return $this->levelUp();
        }

        return true;
    }

    /**
     * Agregar experiencia
     */
    public function addExperience($amount)
    {
        $this->increment('current_experience', $amount);
        $this->increment('total_experience', $amount);

        $leveledUp = false;
        if ($this->canLevelUp()) {
            $leveledUp = $this->levelUp();
        }

        return [
            'leveled_up' => $leveledUp,
            'new_level' => $this->level,
            'current_experience' => $this->current_experience,
            'experience_gained' => $amount
        ];
    }

    /**
     * Obtener progreso del nivel actual (porcentaje)
     */
    public function getLevelProgressAttribute()
    {
        if ($this->experience_to_next_level == 0) {
            return 100;
        }
        
        return round(($this->current_experience / $this->experience_to_next_level) * 100, 2);
    }

    /**
     * Crear o obtener nivel de usuario
     */
    public static function getOrCreateUserLevel($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'level' => 1,
                'current_experience' => 0,
                'total_experience' => 0,
                'experience_to_next_level' => self::calculateExperienceForLevel(1)
            ]
        );
    }
}