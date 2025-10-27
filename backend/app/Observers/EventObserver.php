<?php

namespace App\Observers;

use App\Models\Elementos\Evento;
use App\Traits\HandlesUserXP;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;

class EventObserver
{
    use HandlesUserXP;
    /**
     * Handle the Event "created" event.
     */
    public function created(Evento $event): void
    {
        //
    }

    /**
     * Handle the Event "updated" event.
     */
     public function updated(Evento $event)
    {
        if ($event->isDirty('completed') && $event->completed) {
            $elemento = Elemento::where('id', $event->elemento_id)->first();
            $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
            $this->addXp($user, 'complete_event');
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Evento $event): void
    {
        //
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Evento $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Evento $event): void
    {
        //
    }
}
