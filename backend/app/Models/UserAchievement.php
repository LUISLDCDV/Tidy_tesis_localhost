<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'unlocked_at',
        'progress',
        'is_completed'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
        'progress' => 'integer',
        'is_completed' => 'boolean'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el logro
     */
    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * Obtener progreso como porcentaje
     */
    public function getProgressPercentageAttribute()
    {
        $conditionValue = $this->achievement->condition_value;
        if ($conditionValue == 0) {
            return 100;
        }
        
        return min(100, round(($this->progress / $conditionValue) * 100, 2));
    }

    /**
     * Actualizar progreso del logro
     */
    public function updateProgress($increment = 1)
    {
        $this->increment('progress', $increment);
        
        // Verificar si el logro está completo
        if ($this->progress >= $this->achievement->condition_value && !$this->is_completed) {
            $this->markAsCompleted();
            return true; // Logro desbloqueado
        }
        
        return false; // No desbloqueado aún
    }

    /**
     * Marcar logro como completado
     */
    public function markAsCompleted()
    {
        $this->update([
            'is_completed' => true,
            'unlocked_at' => Carbon::now()
        ]);

        // Dar experiencia al usuario
        $userLevel = UserLevel::getOrCreateUserLevel($this->user_id);
        $result = $userLevel->addExperience($this->achievement->experience_reward);

        return $result;
    }

    /**
     * Crear o actualizar progreso de logro para usuario
     */
    public static function updateAchievementProgress($userId, $achievementId, $increment = 1)
    {
        $userAchievement = self::firstOrCreate([
            'user_id' => $userId,
            'achievement_id' => $achievementId
        ], [
            'progress' => 0,
            'is_completed' => false
        ]);

        return $userAchievement->updateProgress($increment);
    }

    /**
     * Obtener logros completados recientemente
     */
    public static function getRecentlyCompleted($userId, $days = 7)
    {
        return self::with('achievement')
            ->where('user_id', $userId)
            ->where('is_completed', true)
            ->where('unlocked_at', '>=', Carbon::now()->subDays($days))
            ->orderByDesc('unlocked_at')
            ->get();
    }

    /**
     * Obtener logros en progreso
     */
    public static function getInProgress($userId)
    {
        return self::with('achievement')
            ->where('user_id', $userId)
            ->where('is_completed', false)
            ->where('progress', '>', 0)
            ->orderByDesc('progress_percentage')
            ->get();
    }

    /**
     * Verificar y actualizar logros basados en estadísticas del usuario
     */
    public static function checkAndUpdateAchievements($userId, $type, $value = 1)
    {
        $achievements = Achievement::active()->ofType($type)->get();
        $unlockedAchievements = [];

        foreach ($achievements as $achievement) {
            $wasUnlocked = self::updateAchievementProgress($userId, $achievement->id, $value);
            
            if ($wasUnlocked) {
                $unlockedAchievements[] = $achievement;
            }
        }

        return $unlockedAchievements;
    }
}