<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLevel;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\Log;

class LevelController extends Controller
{
    // protected $firebaseService;

    public function __construct()
    {
        // $this->firebaseService = $firebaseService;
    }

    /**
     * Obtener información del nivel del usuario
     */
    public function getUserLevel()
    {
        try {
            $user = auth()->user();

            // Intentar usar UsuarioCuenta primero (para compatibilidad con tests)
            $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

            if ($cuenta) {
                // Calcular XP requerido para el siguiente nivel (fórmula simple)
                $xpRequired = $this->calculateXPForLevel($cuenta->current_level + 1);
                $currentLevelXP = $this->calculateXPForLevel($cuenta->current_level);
                $xpInCurrentLevel = $cuenta->total_xp - $currentLevelXP;
                $xpNeededForNext = $xpRequired - $currentLevelXP;
                $porcentaje = $xpNeededForNext > 0 ? ($xpInCurrentLevel / $xpNeededForNext) * 100 : 100;

                return response()->json([
                    'current_level' => $cuenta->current_level,
                    'total_xp' => $cuenta->total_xp,
                    'xp_required_for_next_level' => $xpRequired,
                    'porcentaje_progreso' => round($porcentaje, 2)
                ], 200);
            }

            // Fallback a UserLevel si no hay UsuarioCuenta
            $userLevel = UserLevel::getOrCreateUserLevel($user->id);

            return response()->json([
                'level_info' => [
                    'level' => $userLevel->level,
                    'current_experience' => $userLevel->current_experience,
                    'total_experience' => $userLevel->total_experience,
                    'experience_to_next_level' => $userLevel->experience_to_next_level,
                    'level_progress' => $userLevel->level_progress
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get user level: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener información de nivel'
            ], 500);
        }
    }

    /**
     * Calcular XP total requerido para alcanzar un nivel
     */
    private function calculateXPForLevel($level)
    {
        // Fórmula: nivel 1 = 0 XP, nivel 2 = 100 XP, nivel 3 = 250 XP, etc.
        // XP = (nivel - 1) * 100 + ((nivel - 1) * (nivel - 2) / 2) * 50
        if ($level <= 1) return 0;
        return ($level - 1) * 100 + (($level - 1) * ($level - 2) / 2) * 50;
    }

    /**
     * Obtener historial de experiencia del usuario
     */
    public function getUserExperience()
    {
        try {
            $user = auth()->user();
            $userLevel = UserLevel::getOrCreateUserLevel($user->id);
            
            // Calcular estadísticas de experiencia
            $experienceStats = [
                'current_level' => $userLevel->level,
                'total_experience' => $userLevel->total_experience,
                'current_level_experience' => $userLevel->current_experience,
                'experience_needed_for_next' => $userLevel->experience_to_next_level - $userLevel->current_experience,
                'progress_percentage' => $userLevel->level_progress,
                'experience_today' => $this->getExperienceToday($user->id),
                'experience_this_week' => $this->getExperienceThisWeek($user->id),
                'experience_this_month' => $this->getExperienceThisMonth($user->id)
            ];

            return response()->json([
                'experience_stats' => $experienceStats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get user experience: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener experiencia del usuario'
            ], 500);
        }
    }

    /**
     * Obtener logros del usuario
     */
    public function getUserAchievements(Request $request)
    {
        try {
            $user = auth()->user();
            $filter = $request->get('filter', 'all'); // all, completed, in_progress

            $query = UserAchievement::with('achievement')
                ->where('user_id', $user->id);

            switch ($filter) {
                case 'completed':
                    $query->where('is_completed', true);
                    break;
                case 'in_progress':
                    $query->where('is_completed', false)
                          ->where('progress', '>', 0);
                    break;
                case 'available':
                    // Logros no iniciados
                    $userAchievementIds = UserAchievement::where('user_id', $user->id)
                        ->pluck('achievement_id');
                    
                    $availableAchievements = Achievement::active()
                        ->whereNotIn('id', $userAchievementIds)
                        ->get()
                        ->map(function ($achievement) {
                            return [
                                'id' => $achievement->id,
                                'achievement' => $achievement,
                                'progress' => 0,
                                'progress_percentage' => 0,
                                'is_completed' => false,
                                'unlocked_at' => null
                            ];
                        });

                    return response()->json([
                        'achievements' => $availableAchievements
                    ], 200);
            }

            $achievements = $query->orderByDesc('unlocked_at')
                ->orderByDesc('progress')
                ->get()
                ->map(function ($userAchievement) {
                    return [
                        'id' => $userAchievement->id,
                        'achievement' => $userAchievement->achievement,
                        'progress' => $userAchievement->progress,
                        'progress_percentage' => $userAchievement->progress_percentage,
                        'is_completed' => $userAchievement->is_completed,
                        'unlocked_at' => $userAchievement->unlocked_at
                    ];
                });

            return response()->json([
                'achievements' => $achievements
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get user achievements: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener logros del usuario'
            ], 500);
        }
    }

    /**
     * Obtener tabla de clasificación
     */
    public function getLeaderboard(Request $request)
    {
        try {
            $limit = $request->get('limit', 20);
            $period = $request->get('period', 'all_time'); // all_time, this_month, this_week
            
            $query = UserLevel::with('user:id,name,email')
                ->orderByDesc('total_experience');

            // Aplicar filtros de período si es necesario
            if ($period !== 'all_time') {
                // En una implementación real, aquí filtrarías por experiencia ganada en el período
                // Por simplicidad, usamos total_experience
            }

            $leaderboard = $query->limit($limit)->get()->map(function ($userLevel, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => [
                        'id' => $userLevel->user->id,
                        'name' => $userLevel->user->name,
                        // No incluir email por privacidad
                    ],
                    'level' => $userLevel->level,
                    'total_experience' => $userLevel->total_experience,
                    'achievements_count' => UserAchievement::where('user_id', $userLevel->user_id)
                        ->where('is_completed', true)
                        ->count()
                ];
            });

            // Obtener posición del usuario actual
            $currentUser = auth()->user();
            $currentUserLevel = UserLevel::where('user_id', $currentUser->id)->first();
            $currentUserRank = null;

            if ($currentUserLevel) {
                $betterUsers = UserLevel::where('total_experience', '>', $currentUserLevel->total_experience)
                    ->count();
                $currentUserRank = $betterUsers + 1;
            }

            return response()->json([
                'leaderboard' => $leaderboard,
                'current_user_rank' => $currentUserRank,
                'total_users' => UserLevel::count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get leaderboard: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener tabla de clasificación'
            ], 500);
        }
    }

    /**
     * Dar experiencia manualmente (para testing)
     */
    public function giveExperience(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1|max:1000',
            'reason' => 'required|string|max:255'
        ]);

        try {
            $user = auth()->user();
            $userLevel = UserLevel::getOrCreateUserLevel($user->id);
            
            $result = $userLevel->addExperience($request->amount);

            // Log de la acción
            Log::info('Experience given manually', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'result' => $result
            ]);

            // Enviar notificación si subió de nivel
            if ($result['leveled_up']) {
                // TODO: Implementar notificación cuando Firebase esté configurado
                // $this->firebaseService->sendSystemNotification(
                //     $user->id,
                //     'level_up',
                //     '¡Felicitaciones! 🎉',
                //     "¡Has alcanzado el nivel {$result['new_level']}!",
                //     [
                //         'new_level' => $result['new_level'],
                //         'experience_gained' => $result['experience_gained']
                //     ]
                // );
                Log::info('User leveled up', [
                    'user_id' => $user->id,
                    'new_level' => $result['new_level'],
                    'experience_gained' => $result['experience_gained']
                ]);
            }

            return response()->json([
                'message' => 'Experiencia otorgada exitosamente',
                'result' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to give experience: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al otorgar experiencia'
            ], 500);
        }
    }

    /**
     * Métodos auxiliares para estadísticas
     */
    private function getExperienceToday($userId)
    {
        // En una implementación real, necesitarías un log de experiencia diaria
        // Por simplicidad, retornamos un valor simulado
        return rand(0, 200);
    }

    private function getExperienceThisWeek($userId)
    {
        // Simulado
        return rand(200, 800);
    }

    private function getExperienceThisMonth($userId)
    {
        // Simulado
        return rand(800, 2500);
    }
}