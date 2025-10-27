<?php

namespace App\Observers;

use App\Traits\HandlesUserXP;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Alarma;
use App\Models\UsuarioCuenta;

class AlarmObserver
{
    use HandlesUserXP;
    
    /**
     * Handle the Alarm "created" event.
     */
    public function created(Alarma $alarma)
    {
        $elemento = Elemento::where('id', $alarma->elemento_id)->first();
        $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
        $this->addXp($user, 'create_alarm');
    }

    /**
     * Handle the Alarm "updated" event.
     */
    public function updated(Alarma $alarma): void
    {
        //
    }

    /**
     * Handle the Alarm "deleted" event.
     */
    public function deleted(Alarma $alarma): void
    {
        //
    }

    /**
     * Handle the Alarm "restored" event.
     */
    public function restored(Alarma $alarma): void
    {
        //
    }

    /**
     * Handle the Alarm "force deleted" event.
     */
    public function forceDeleted(Alarma $alarma): void
    {
        //
    }
}
