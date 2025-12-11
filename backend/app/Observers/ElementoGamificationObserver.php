<?php

namespace App\Observers;

use App\Models\Elementos\Elemento;
use App\Services\GamificationService;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class ElementoGamificationObserver
{
    protected $gamificationService;
    protected $firebaseService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
        $this->firebaseService = null; // Disable Firebase for now
        
        // TODO: Reactivar cuando Firebase esté configurado
        // try {
        //     if (class_exists('\Kreait\Firebase\Factory')) {
        //         $this->firebaseService = app(FirebaseService::class);
        //     }
        // } catch (\Exception $e) {
        //     Log::warning('Firebase service unavailable: ' . $e->getMessage());
        //     $this->firebaseService = null;
        // }
    }

    /**
     * Manejar evento cuando se crea un elemento.
     */
    public function created(Elemento $elemento)
    {
        try {
            Log::info('ElementoGamificationObserver triggered', [
                'elemento_id' => $elemento->id,
                'elemento_tipo' => $elemento->tipo,
                'cuenta_id' => $elemento->cuenta_id
            ]);

            // Obtener el user_id a través de la cuenta
            $cuenta = $elemento->cuenta;
            if (!$cuenta) {
                Log::warning('Elemento created without cuenta', [
                    'elemento_id' => $elemento->id,
                    'cuenta_id_attribute' => $elemento->cuenta_id,
                    'elemento_attributes' => $elemento->getAttributes()
                ]);
                return;
            }

            $userId = $cuenta->user_id;
            if (!$userId) {
                Log::warning('Cuenta without user_id', [
                    'elemento_id' => $elemento->id,
                    'cuenta_id' => $cuenta->id,
                    'cuenta_attributes' => $cuenta->getAttributes()
                ]);
                return;
            }

            Log::info('Processing gamification for element creation', [
                'elemento_id' => $elemento->id,
                'elemento_tipo' => $elemento->tipo,
                'user_id' => $userId,
                'cuenta_id' => $cuenta->id
            ]);

            // Procesar gamificación por creación de elemento
            $result = $this->gamificationService->processElementCreated(
                $userId,
                $elemento->tipo
            );

            Log::info('Gamification processed for element creation', [
                'user_id' => $userId,
                'elemento_id' => $elemento->id,
                'elemento_tipo' => $elemento->tipo,
                'experience_gained' => $result['experience_result']['experience_gained'] ?? 0,
                'achievements_unlocked' => count($result['unlocked_achievements'] ?? [])
            ]);

            // Enviar notificación de elemento creado
            $this->sendElementCreatedNotification($userId, $elemento);

        } catch (\Exception $e) {
            Log::error('Error processing element creation gamification: ' . $e->getMessage(), [
                'elemento_id' => $elemento->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Manejar evento cuando se actualiza un elemento.
     */
    public function updated(Elemento $elemento)
    {
        try {
            $cuenta = $elemento->cuenta;
            if (!$cuenta) {
                return;
            }

            $userId = $cuenta->user_id;
            if (!$userId) {
                return;
            }

            // Solo dar experiencia por actualizaciones significativas
            if ($elemento->wasChanged(['descripcion', 'contenido', 'estado'])) {
                $this->gamificationService->giveExperienceForAction(
                    $userId,
                    'element_updated'
                );

                Log::info('Experience given for element update', [
                    'user_id' => $userId,
                    'elemento_id' => $elemento->id,
                    'changes' => $elemento->getChanges()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error processing element update gamification: ' . $e->getMessage());
        }
    }

    /**
     * Manejar evento cuando se elimina un elemento (soft delete).
     */
    public function deleted(Elemento $elemento)
    {
        try {
            $cuenta = $elemento->cuenta;
            if (!$cuenta) {
                return;
            }

            $userId = $cuenta->user_id;
            if (!$userId) {
                return;
            }

            // Dar una pequeña cantidad de experiencia por eliminar elementos
            $this->gamificationService->giveExperienceForAction(
                $userId,
                'element_deleted'
            );

            Log::info('Experience given for element deletion', [
                'user_id' => $userId,
                'elemento_id' => $elemento->id,
                'elemento_tipo' => $elemento->tipo
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing element deletion gamification: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación de elemento creado
     */
    private function sendElementCreatedNotification($userId, $elemento)
    {
        if (!$this->firebaseService) {
            Log::info('Skipping Firebase notification - service unavailable');
            return;
        }

        // Mapear tipos de elementos a emojis
        $typeEmojis = [
            'nota' => '📝',
            'alarma' => '⏰',
            'objetivo' => '🎯',
            'calendario' => '📅',
            'evento' => '📅',
            'meta' => '✅'
        ];

        $emoji = $typeEmojis[$elemento->tipo] ?? '📋';
        
        try {
            $this->firebaseService->sendSystemNotification(
                $userId,
                'element_created',
                "Nuevo {$elemento->tipo} creado! {$emoji}",
                $elemento->descripcion,
                [
                    'type' => 'element_created',
                    'element_id' => $elemento->id,
                    'element_type' => $elemento->tipo,
                    'element_description' => $elemento->descripcion,
                    'click_action' => 'OPEN_ELEMENT'
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send Firebase notification: ' . $e->getMessage());
        }
    }
}