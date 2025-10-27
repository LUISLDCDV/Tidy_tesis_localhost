<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Mostrar vista de gestión de usuarios
     */
    public function index(Request $request)
    {
        try {
            // Debug: verificar si el método userLevel existe
            $userModel = new User();
            if (!method_exists($userModel, 'userLevel')) {
                throw new \Exception('Method userLevel does not exist on User model');
            }

            // Obtener usuarios con niveles y cuenta - con manejo de errores mejorado
            try {
                $query = User::with(['userLevel', 'cuenta'])
                    ->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'admin');
                    });
            } catch (\Exception $e) {
                // Si falla la relación, usar consulta sin eager loading
                $query = User::whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'admin');
                });
            }

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('level')) {
                $query->whereHas('userLevel', function ($q) use ($request) {
                    $q->where('level', $request->level);
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            if ($sortBy === 'experience') {
                $query->join('user_levels', 'users.id', '=', 'user_levels.user_id')
                      ->orderBy('user_levels.total_experience', $sortDirection)
                      ->select('users.*');
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            // Transformar datos para la vista - asegurar que userLevel esté disponible
            $transformedUsers = $users->getCollection()->map(function ($user) {
                // Si no tiene userLevel cargado, intentar cargarlo manualmente
                if (!$user->relationLoaded('userLevel')) {
                    try {
                        $user->load('userLevel');
                    } catch (\Exception $e) {
                        // Si falla cargar la relación, crear datos por defecto
                        $user->setRelation('userLevel', (object)[
                            'level' => 0,
                            'total_experience' => 0,
                            'current_experience' => 0
                        ]);
                    }
                }

                // Si aún no tiene userLevel, crear uno por defecto
                if (!$user->userLevel) {
                    $user->setRelation('userLevel', (object)[
                        'level' => 0,
                        'total_experience' => 0,
                        'current_experience' => 0
                    ]);
                }

                return $user;
            });

            $users->setCollection($transformedUsers);

            $usersData = [
                'data' => $users->items(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage()
            ];

            return view('admin.dashboard.users', compact('usersData'));

        } catch (\Exception $e) {
            Log::error('Error en gestión de usuarios: ' . $e->getMessage());

            // Crear datos de ejemplo para cuando la BD no esté disponible
            $sampleUsers = [
                [
                    'id' => 1,
                    'name' => 'Usuario',
                    'last_name' => 'Demo',
                    'email' => 'demo@tidy.com',
                    'phone' => '+1234567890',
                    'created_at' => now()->toISOString(),
                    'userLevel' => [
                        'level' => 1,
                        'total_experience' => 100,
                        'current_experience' => 50
                    ]
                ],
                [
                    'id' => 2,
                    'name' => 'Usuario',
                    'last_name' => 'Test',
                    'email' => 'test@tidy.com',
                    'phone' => '+0987654321',
                    'created_at' => now()->subDays(1)->toISOString(),
                    'userLevel' => [
                        'level' => 2,
                        'total_experience' => 350,
                        'current_experience' => 120
                    ]
                ]
            ];

            $sampleUsersData = [
                'data' => $sampleUsers,
                'current_page' => 1,
                'last_page' => 1,
                'total' => 2,
                'per_page' => 15
            ];

            return view('admin.dashboard.users', [
                'usersData' => $sampleUsersData,
                'error' => 'Mostrando datos de ejemplo - Error BD: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Editar experiencia de usuario
     */
    public function editExperience(Request $request, User $user)
    {
        $request->validate([
            'experience' => 'required|integer|min:0|max:757500',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $userLevel = $user->userLevel ?? new UserLevel(['user_id' => $user->id]);
            $oldExperience = $userLevel->total_experience ?? 0;
            $newExperience = $request->experience;

            // Actualizar experiencia
            $userLevel->total_experience = $newExperience;

            // Recalcular nivel basado en nueva experiencia
            $newLevel = $this->calculateLevelFromExperience($newExperience);
            $oldLevel = $userLevel->level ?? 0;
            $userLevel->level = $newLevel;

            $userLevel->save();

            // Log de la acción
            Log::info('Admin editó experiencia de usuario', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'old_experience' => $oldExperience,
                'new_experience' => $newExperience,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'reason' => $request->reason
            ]);

            // Notificar al usuario si cambió de nivel
            if ($newLevel > $oldLevel) {
                $this->sendLevelUpNotification($user, $newLevel);
            }

            DB::commit();

            return redirect()->back()->with('success',
                "Experiencia de {$user->name} actualizada de {$oldExperience} XP a {$newExperience} XP. " .
                ($newLevel !== $oldLevel ? "Nivel actualizado a {$newLevel}." : "")
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error editando experiencia: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error actualizando experiencia: ' . $e->getMessage()
            );
        }
    }

    /**
     * Editar nivel de usuario
     */
    public function editLevel(Request $request, User $user)
    {
        $request->validate([
            'level' => 'required|integer|min:0|max:100',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $userLevel = $user->userLevel ?? new UserLevel(['user_id' => $user->id]);
            $oldLevel = $userLevel->level ?? 0;
            $newLevel = $request->level;

            $userLevel->level = $newLevel;
            $userLevel->save();

            Log::info('Admin editó nivel de usuario', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'reason' => $request->reason
            ]);

            DB::commit();

            return redirect()->back()->with('success',
                "Nivel de {$user->name} actualizado de {$oldLevel} a {$newLevel}."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error editando nivel: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error actualizando nivel: ' . $e->getMessage()
            );
        }
    }

    /**
     * Gestionar estado Premium de usuario
     */
    public function editPremium(Request $request, User $user)
    {
        $request->validate([
            'is_premium' => 'required|boolean',
            'premium_expires_at' => 'nullable|date|after:today',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Obtener o crear la cuenta del usuario
            $cuenta = \App\Models\UsuarioCuenta::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'total_xp' => 0,
                    'current_level' => 1,
                    'configuraciones' => json_encode([])
                ]
            );

            $oldPremium = $cuenta->is_premium ?? false;
            $cuenta->is_premium = $request->is_premium;

            if ($request->is_premium) {
                $cuenta->premium_expires_at = $request->premium_expires_at ?? now()->addYear();
            } else {
                $cuenta->premium_expires_at = null;
            }

            $cuenta->save();

            Log::info('Admin modificó estado Premium de usuario', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'old_premium' => $oldPremium,
                'new_premium' => $request->is_premium,
                'expires_at' => $cuenta->premium_expires_at,
                'reason' => $request->reason
            ]);

            DB::commit();

            $status = $request->is_premium ? 'activado' : 'desactivado';
            return redirect()->back()->with('success',
                "Estado Premium de {$user->name} {$status}."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error editando premium: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error actualizando premium: ' . $e->getMessage()
            );
        }
    }

    /**
     * Soft delete de usuario
     */
    public function softDelete(User $user, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Soft delete del usuario
            $user->delete();

            // Log de la acción
            Log::warning('Admin eliminó usuario (soft delete)', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'reason' => $request->reason
            ]);

            // Notificar al usuario por email
            $this->sendAccountDeactivationEmail($user, $request->reason);

            DB::commit();

            return redirect()->back()->with('success',
                "Usuario {$user->name} ha sido desactivado. Se le ha enviado un email de notificación."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en soft delete de usuario: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error desactivando usuario: ' . $e->getMessage()
            );
        }
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restore($userId, Request $request)
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);

            $user->restore();

            Log::info('Admin restauró usuario', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Notificar al usuario por email
            $this->sendAccountReactivationEmail($user);

            return redirect()->back()->with('success',
                "Usuario {$user->name} ha sido reactivado. Se le ha enviado un email de notificación."
            );

        } catch (\Exception $e) {
            Log::error('Error restaurando usuario: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error reactivando usuario: ' . $e->getMessage()
            );
        }
    }

    /**
     * Enviar notificación personalizada a usuario
     */
    public function sendNotification(Request $request, User $user)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,success,warning,error',
            'send_email' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Crear notificación en la app
            $notification = new Notification([
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'is_read' => false,
                'created_by_admin' => true,
                'admin_id' => auth()->id()
            ]);

            $notification->save();

            // Enviar email si se solicita
            if ($request->send_email) {
                $this->sendCustomNotificationEmail($user, $request->title, $request->message);
            }

            Log::info('Admin envió notificación a usuario', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'title' => $request->title,
                'send_email' => $request->send_email
            ]);

            DB::commit();

            return redirect()->back()->with('success',
                "Notificación enviada a {$user->name}." .
                ($request->send_email ? " También se envió por email." : "")
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error enviando notificación: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error enviando notificación: ' . $e->getMessage()
            );
        }
    }

    /**
     * Enviar email de prueba a usuario
     */
    public function sendTestEmail(Request $request, User $user)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000'
        ]);

        try {
            $this->sendTestEmailToUser($user, $request->subject, $request->message);

            Log::info('Admin envió email de prueba', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'subject' => $request->subject
            ]);

            return redirect()->back()->with('success',
                "Email de prueba enviado a {$user->email}"
            );

        } catch (\Exception $e) {
            Log::error('Error enviando email de prueba: ' . $e->getMessage());

            return redirect()->back()->with('error',
                'Error enviando email: ' . $e->getMessage()
            );
        }
    }

    /**
     * Calcular nivel basado en experiencia
     */
    private function calculateLevelFromExperience($experience)
    {
        if ($experience < 100) return 0;
        if ($experience < 250) return 1;
        if ($experience < 500) return 2;
        if ($experience < 1000) return 3;
        if ($experience < 2000) return 4;
        if ($experience < 5000) return 5;
        if ($experience < 10000) return 6;
        if ($experience < 20000) return 7;
        if ($experience < 50000) return 8;

        return 9; // Nivel máximo
    }

    /**
     * Enviar notificación de subida de nivel
     */
    private function sendLevelUpNotification($user, $level)
    {
        try {
            Mail::send('emails.level-up', [
                'user' => $user,
                'level' => $level
            ], function ($message) use ($user, $level) {
                $message->to($user->email)
                        ->subject("🎉 ¡Felicidades! Has alcanzado el nivel {$level}");
            });

            Log::info('Email de subida de nivel enviado', [
                'user_id' => $user->id,
                'level' => $level
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando email de subida de nivel: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de desactivación de cuenta
     */
    private function sendAccountDeactivationEmail($user, $reason)
    {
        try {
            Mail::send('emails.account-deactivated', [
                'user' => $user,
                'reason' => $reason
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Tu cuenta Tidy ha sido desactivada');
            });

        } catch (\Exception $e) {
            Log::error('Error enviando email de desactivación: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de reactivación de cuenta
     */
    private function sendAccountReactivationEmail($user)
    {
        try {
            Mail::send('emails.account-reactivated', [
                'user' => $user
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('¡Tu cuenta Tidy ha sido reactivada!');
            });

        } catch (\Exception $e) {
            Log::error('Error enviando email de reactivación: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación personalizada por email
     */
    private function sendCustomNotificationEmail($user, $title, $message)
    {
        try {
            Mail::send('emails.custom-notification', [
                'user' => $user,
                'title' => $title,
                'message' => $message
            ], function ($mail) use ($user, $title) {
                $mail->to($user->email)
                     ->subject($title);
            });

        } catch (\Exception $e) {
            Log::error('Error enviando email personalizado: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de prueba
     */
    private function sendTestEmailToUser($user, $subject, $message)
    {
        try {
            Mail::send('emails.test-email', [
                'user' => $user,
                'message' => $message
            ], function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject('[PRUEBA] ' . $subject);
            });

        } catch (\Exception $e) {
            Log::error('Error enviando email de prueba: ' . $e->getMessage());
            throw $e;
        }
    }
}