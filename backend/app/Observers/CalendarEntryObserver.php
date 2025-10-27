<?php

namespace App\Observers;

use App\Models\Elementos\Calendario;
use App\Traits\HandlesUserXP;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;

class CalendarEntryObserver
{
    use HandlesUserXP;
    /**
     * Handle the Calendario "created" event.
     */
    public function created(Calendario $calendario)
    {
        $elemento = Elemento::where('id', $calendario->elemento_id)->first();
        $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
        $this->addXp($user, 'use_calendar');
    }

    /**
     * Handle the Calendario "updated" event.
     */
    public function updated(Calendario $calendario): void
    {
        //
    }

    /**
     * Handle the Calendario "deleted" event.
     */
    public function deleted(Calendario $calendario): void
    {
        //
    }

    /**
     * Handle the Calendario "restored" event.
     */
    public function restored(Calendario $calendario): void
    {
        //
    }

    /**
     * Handle the Calendario "force deleted" event.
     */
    public function forceDeleted(Calendario $calendario): void
    {
        //
    }
}
