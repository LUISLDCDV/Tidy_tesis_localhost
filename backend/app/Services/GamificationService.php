<?php

namespace App\Services;

use App\Models\UserLevel;
use App\Models\UserAchievement;
use App\Models\Achievement;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = null; // Disable Firebase for now
        
        // TODO: Reactivar cuando Firebase esté configurado
        // try {
        //     if (class_exists('\Kreait\Firebase\Factory')) {
        //         $this->firebaseService = app(FirebaseService::class);
        //     }
        // } catch (\Exception $e) {
        //     Log::warning('Firebase service unavailable in GamificationService: ' . $e->getMessage());
        //     $this->firebaseService = null;
        // }
    }

    /**
     * Experiencia base para diferentes acciones
     */
    const EXPERIENCE_REWARDS = [
        'element_created' => 20,
        'element_updated' => 10,
        'element_deleted' => 5,
        'goal_completed' => 50,
        'note_created' => 15,
        'alarm_created' => 15,
        'calendar_created' => 25,
        'daily_login' => 10,
        'weekly_streak' => 100,
        'achievement_unlocked' => 0, // Se define por el logro
    ];

    /**
     * Dar experiencia por una acción específica
     */
    public function giveExperienceForAction($userId, $action, $multiplier = 1)
    {
        if (!isset(self::EXPERIENCE_REWARDS[$action])) {
            Log::warning('Unknown action for experience reward: ' . $action);
            return null;
        }

        $baseExperience = self::EXPERIENCE_REWARDS[$action];
        $experienceAmount = $baseExperience * $multiplier;

        return $this->giveExperience($userId, $experienceAmount, $action);
    }

    /**
     * Dar experiencia directamente
     */
    public function giveExperience($userId, $amount, $reason = 'manual')
    {
        try {
            $userLevel = UserLevel::getOrCreateUserLevel($userId);
            $result = $userLevel->addExperience($amount);

            Log::info('Experience awarded', [
                'user_id' => $userId,
                'amount' => $amount,
                'reason' => $reason,
                'leveled_up' => $result['leveled_up'],
                'new_level' => $result['new_level']
            ]);

            // Enviar notificación si subió de nivel
            if ($result['leveled_up']) {
                $this->sendLevelUpNotification($userId, $result['new_level']);
                
                // Verificar logros de nivel
                $this->checkLevelAchievements($userId, $result['new_level']);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to give experience: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Procesar creación de elemento
     */
    public function processElementCreated($userId, $elementType = 'general')
    {
        // Dar experiencia base
        $experienceResult = $this->giveExperienceForAction($userId, 'element_created');

        // Verificar y actualizar logros relacionados
        $unlockedAchievements = UserAchievement::checkAndUpdateAchievements(
            $userId, 
            'element_created'
        );

        // Verificar logros especiales basados en hora
        $this->checkTimeBasedAchievements($userId);

        // Enviar notificaciones por logros desbloqueados
        foreach ($unlockedAchievements as $achievement) {
            $this->sendAchievementNotification($userId, $achievement);
        }

        return [
            'experience_result' => $experienceResult,
            'unlocked_achievements' => $unlockedAchievements
        ];
    }

    /**
     * Procesar objetivo completado
     */
    public function processGoalCompleted($userId, $objetivoId = null)
    {
        // Dar experiencia por objetivo completado
        $experienceResult = $this->giveExperienceForAction($userId, 'goal_completed');

        // Verificar logros de objetivos
        $unlockedAchievements = UserAchievement::checkAndUpdateAchievements(
            $userId,
            'goal_completed'
        );

        // Si se proporciona el ID del objetivo, verificar logro de "objetivo con muchas metas"
        if ($objetivoId) {
            $metasCount = \App\Models\Elementos\Meta::where('objetivo_id', $objetivoId)->count();

            if ($metasCount > 5) {
                $metasAchievements = UserAchievement::checkAndUpdateAchievements(
                    $userId,
                    'goal_with_many_metas',
                    $metasCount
                );
                $unlockedAchievements = array_merge($unlockedAchievements, $metasAchievements);
            }
        }

        // Notificaciones
        foreach ($unlockedAchievements as $achievement) {
            $this->sendAchievementNotification($userId, $achievement);
        }

        return [
            'experience_result' => $experienceResult,
            'unlocked_achievements' => $unlockedAchievements
        ];
    }

    /**
     * Procesar login diario
     */
    public function processDailyLogin($userId)
    {
        // Verificar si ya se dio experiencia por login hoy
        $lastLogin = $this->getLastLoginDate($userId);
        $today = now()->toDateString();

        if ($lastLogin !== $today) {
            $experienceResult = $this->giveExperienceForAction($userId, 'daily_login');
            $this->recordDailyLogin($userId);

            // Verificar rachas diarias
            $this->checkDailyStreakAchievements($userId);

            return $experienceResult;
        }

        return null;
    }

    /**
     * Verificar logros basados en nivel alcanzado
     */
    private function checkLevelAchievements($userId, $level)
    {
        $levelAchievements = Achievement::active()
            ->where('type', 'level_reached')
            ->where('condition_value', '<=', $level)
            ->get();

        foreach ($levelAchievements as $achievement) {
            $userAchievement = UserAchievement::where('user_id', $userId)
                ->where('achievement_id', $achievement->id)
                ->first();

            if (!$userAchievement || !$userAchievement->is_completed) {
                $wasUnlocked = UserAchievement::updateAchievementProgress(
                    $userId, 
                    $achievement->id, 
                    $level
                );

                if ($wasUnlocked) {
                    $this->sendAchievementNotification($userId, $achievement);
                }
            }
        }
    }

    /**
     * Verificar logros basados en tiempo
     */
    private function checkTimeBasedAchievements($userId)
    {
        $hour = now()->hour;
        
        // Madrugador (antes de las 6 AM)
        if ($hour < 6) {
            $achievement = Achievement::where('type', 'special')
                ->where('condition_type', 'time_based')
                ->where('condition_value', 6)
                ->first();

            if ($achievement) {
                $wasUnlocked = UserAchievement::updateAchievementProgress($userId, $achievement->id);
                if ($wasUnlocked) {
                    $this->sendAchievementNotification($userId, $achievement);
                }
            }
        }

        // Noctámbulo (después de las 11 PM)
        if ($hour >= 23) {
            $achievement = Achievement::where('type', 'special')
                ->where('condition_type', 'time_based')
                ->where('condition_value', 23)
                ->first();

            if ($achievement) {
                $wasUnlocked = UserAchievement::updateAchievementProgress($userId, $achievement->id);
                if ($wasUnlocked) {
                    $this->sendAchievementNotification($userId, $achievement);
                }
            }
        }
    }

    /**
     * Verificar logros de racha diaria
     */
    private function checkDailyStreakAchievements($userId)
    {
        $streakDays = $this->getUserStreakDays($userId);
        
        $streakAchievements = Achievement::active()
            ->where('type', 'daily_streak')
            ->where('condition_value', '<=', $streakDays)
            ->get();

        foreach ($streakAchievements as $achievement) {
            $wasUnlocked = UserAchievement::updateAchievementProgress(
                $userId, 
                $achievement->id, 
                $streakDays
            );

            if ($wasUnlocked) {
                $this->sendAchievementNotification($userId, $achievement);
            }
        }
    }

    /**
     * Enviar notificación de subida de nivel
     */
    private function sendLevelUpNotification($userId, $newLevel)
    {
        if (!$this->firebaseService) {
            Log::info('Skipping level up notification - Firebase service unavailable');
            return;
        }
        
        try {
            $this->firebaseService->sendSystemNotification(
                $userId,
                'level_up',
                '¡Subiste de nivel! 🎉',
                "¡Felicitaciones! Has alcanzado el nivel {$newLevel}",
                [
                    'type' => 'level_up',
                    'new_level' => $newLevel,
                    'click_action' => 'OPEN_PROFILE'
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send level up notification: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación de logro desbloqueado
     */
    private function sendAchievementNotification($userId, $achievement)
    {
        if (!$this->firebaseService) {
            Log::info('Skipping achievement notification - Firebase service unavailable');
            return;
        }
        
        try {
            $this->firebaseService->sendSystemNotification(
                $userId,
                'achievement_unlocked',
                '¡Nuevo logro desbloqueado! 🏆',
                "{$achievement->icon} {$achievement->name}: {$achievement->description}",
                [
                'type' => 'achievement_unlocked',
                'achievement_id' => $achievement->id,
                'achievement_name' => $achievement->name,
                'experience_reward' => $achievement->experience_reward,
                'click_action' => 'OPEN_ACHIEVEMENTS'
            ]
        );
        } catch (\Exception $e) {
            Log::error('Failed to send achievement notification: ' . $e->getMessage());
        }
    }

    /**
     * Obtener fecha del último login (simulado)
     */
    private function getLastLoginDate($userId)
    {
        // En una implementación real, esto vendría de una tabla de logs de usuario
        // Por simplicidad, usamos cache o Firebase Database
        if (!$this->firebaseService) {
            // Fallback: return null para simular que no hay último login
            return null;
        }
        
        try {
            return $this->firebaseService->getFromDatabase("user_activity/{$userId}/last_login_date");
        } catch (\Exception $e) {
            Log::error('Failed to get last login date: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar login diario
     */
    private function recordDailyLogin($userId)
    {
        if (!$this->firebaseService) {
            Log::info('Skipping daily login record - Firebase service unavailable');
            return;
        }
        
        try {
            $today = now()->toDateString();
            $this->firebaseService->saveToDatabase("user_activity/{$userId}/last_login_date", $today);
            
            // También incrementar contador de días consecutivos
            $streakDays = $this->getUserStreakDays($userId) + 1;
            $this->firebaseService->saveToDatabase("user_activity/{$userId}/streak_days", $streakDays);
        } catch (\Exception $e) {
            Log::error('Failed to record daily login: ' . $e->getMessage());
        }
    }

    /**
     * Obtener días consecutivos del usuario
     */
    private function getUserStreakDays($userId)
    {
        if (!$this->firebaseService) {
            return 0; // Fallback: no streak days
        }
        
        try {
            return $this->firebaseService->getFromDatabase("user_activity/{$userId}/streak_days") ?? 0;
        } catch (\Exception $e) {
            Log::error('Failed to get user streak days: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verificar y otorgar logro de verificación de email
     */
    public function checkEmailVerificationAchievement($user)
    {
        if (!$user->hasVerifiedEmail()) {
            return; // El email no está verificado
        }

        try {
            // Buscar el logro de verificación de email
            $achievement = Achievement::where('type', 'email_verified')
                ->where('is_active', true)
                ->first();

            if (!$achievement) {
                Log::warning('Email verification achievement not found');
                return;
            }

            // Verificar si el usuario ya tiene este logro
            $existingAchievement = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->first();

            if ($existingAchievement) {
                Log::info('User already has email verification achievement', [
                    'user_id' => $user->id
                ]);
                return;
            }

            // Otorgar el logro
            $userAchievement = UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'progress' => 1,
                'progress_percentage' => 100,
                'is_completed' => true,
                'completed_at' => now()
            ]);

            // Dar experiencia por el logro
            $this->giveExperience($user->id, $achievement->experience_reward, 'email_verification_achievement');

            // Enviar notificación
            $this->sendAchievementNotification($user->id, $achievement);

            Log::info('Email verification achievement granted', [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'experience_reward' => $achievement->experience_reward
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check email verification achievement', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener resumen de gamificación del usuario
     */
    public function getUserGamificationSummary($userId)
    {
        $userLevel = UserLevel::getOrCreateUserLevel($userId);
        
        $completedAchievements = UserAchievement::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();

        $totalAchievements = Achievement::active()->count();

        $recentAchievements = UserAchievement::getRecentlyCompleted($userId, 7);

        return [
            'level' => $userLevel->level,
            'total_experience' => $userLevel->total_experience,
            'current_level_progress' => $userLevel->level_progress,
            'completed_achievements' => $completedAchievements,
            'total_achievements' => $totalAchievements,
            'achievement_completion_rate' => $totalAchievements > 0 
                ? round(($completedAchievements / $totalAchievements) * 100, 2) 
                : 0,
            'recent_achievements' => $recentAchievements,
            'streak_days' => $this->getUserStreakDays($userId)
        ];
    }
}