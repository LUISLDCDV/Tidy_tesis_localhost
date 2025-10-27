<?php

namespace App\Observers;

use App\Models\Elementos\Objetivo;
use App\Models\Elementos\Meta;
use App\Traits\HandlesUserXP;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;
use App\Services\GamificationService;

class ObjetivoObserver
{
    use HandlesUserXP;
    
    /**
     * Handle the Goal "created" event.
     */
    public function created(Objetivo $objetivo)
    {
        $elemento = Elemento::where('id', $objetivo->elemento_id)->first();
        $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
        $this->addXp($user, 'create_goal');
    }

    /**
     * Handle the Goal "updated" event.
     */
    public function updated(Objetivo $objetivo)
    {
        // Verificar si el objetivo se marcó como completado
        if ($objetivo->isDirty('status') && $objetivo->status === 'completado') {
            $elemento = Elemento::where('id', $objetivo->elemento_id)->first();
            $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();

            // XP tradicional
            $this->addXp($user, 'complete_goal');

            // Nuevo sistema de gamificación
            $gamificationService = app(GamificationService::class);
            $gamificationService->processGoalCompleted($user->user_id, $objetivo->id);
        }
    }

    /**
     * Handle the Goal "deleted" event.
     */
    public function deleted(Objetivo $objetivo): void
    {
        //
    }

    /**
     * Handle the Goal "restored" event.
     */
    public function restored(Objetivo $objetivo): void
    {
        //
    }

    /**
     * Handle the Goal "force deleted" event.
     */
    public function forceDeleted(Objetivo $objetivo): void
    {
        //
    }
}
