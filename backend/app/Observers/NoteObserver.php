<?php

namespace App\Observers;

use App\Models\Elementos\Nota;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;

use App\Traits\HandlesUserXP;

class NoteObserver
{
    
    use HandlesUserXP;

    /**
     * Handle the Note "created" event.
     */
    public function created(Nota $note): void
    {
        $elemento = Elemento::where('id', $note->elemento_id)->first();
        $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
        $this->addXp($user, 'create_note');
    }



    /**
     * Handle the Note "updated" event.
     */
    public function updated(Nota $note): void
    {
        //
    }

    /**
     * Handle the Note "deleted" event.
     */
    public function deleted(Nota $note): void
    {
        //
    }

    /**
     * Handle the Note "restored" event.
     */
    public function restored(Nota $note): void
    {
        //
    }

    /**
     * Handle the Note "force deleted" event.
     */
    public function forceDeleted(Nota $note): void
    {
        //
    }
}
