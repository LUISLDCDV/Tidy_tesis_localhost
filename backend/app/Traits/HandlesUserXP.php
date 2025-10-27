<?php
// app/Traits/HandlesUserXP.php

namespace App\Traits;

trait HandlesUserXP
{
    // XP por acciones (puedes mover esto a un config si prefieres)
    protected $xpActions = [
        'create_note'       => 10,
        'update_note'       => 1,  // XP por editar una nota
        'complete_event'    => 30,
        'create_alarm'      => 15,
        'complete_goal'     => 50,
        'create_goal'       => 20,
        'use_calendar'      => 5,
        'create_meta'       => 25,
        'complete_meta'     => 40,
        'create_task'       => 10,
    ];

    // Límites diarios (opcional)
    protected $dailyLimits = [
        'create_note' => 100, // Máximo 100 XP por día (10 notas)
        'complete_event' => 300,
        // ... otros límites
    ];

    /**
     * Añade XP al usuario y verifica si sube de nivel.
     */
    public function addXp($user, $action)
    {
        // Verificar si la acción existe
        if (!isset($this->xpActions[$action])) {
            return false;
        }

        $xpToAdd = $this->xpActions[$action];

        // Verificar límite diario (ejemplo con Redis o DB)
        if ($this->hasReachedDailyLimit($user, $action)) {
            return false;
        }

        // Actualizar XP del usuario
        $user->increment('total_xp', $xpToAdd);

        // Verificar si sube de nivel
        $this->checkLevelUp($user);

        return true;
    }

    /**
     * Verifica si el usuario alcanzó un nuevo nivel.
     */
    protected function checkLevelUp($user)
    {
        $currentLevel = $user->current_level;
        $newLevel = $this->calculateLevel($user->total_xp);

        if ($newLevel > $currentLevel) {
            $user->update(['current_level' => $newLevel]);
            // Opcional: Notificar al usuario (ej. con evento)
        }
    }

    /**
     * Fórmula para calcular el nivel basado en XP.
     */
    protected function calculateLevel($totalXp)
    {
        return (int) max(1, floor(sqrt($totalXp / 100)));
    }

    /**
     * Verifica si el usuario ya alcanzó el límite diario de XP para una acción.
     */
    protected function hasReachedDailyLimit($user, $action)
    {
        // Implementar lógica de límite diario (ejemplo con Redis o DB)
        return false; // Por ahora, sin límite
    }
}