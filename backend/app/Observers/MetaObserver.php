<?php

namespace App\Observers;

use App\Models\Elementos\Objetivo;
use App\Models\Elementos\Meta;
use App\Traits\HandlesUserXP;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;

class MetaObserver
{
    use HandlesUserXP;
    
    /**
     * Handle the Meta "created" event.
     */
    public function created(Meta $meta)
    {
        $elemento = Elemento::where('id', $meta->elemento_id)->first();
        $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
        $this->addXp($user, 'create_meta');
    }

    /**
     * Handle the Meta "updated" event.
     */
    public function updated(Meta $meta)
    {
        if ($meta->isDirty('completed') && $meta->completed) {
            $elemento = Elemento::where('id', $meta->elemento_id)->first();
            $user = UsuarioCuenta::where('id', $elemento->cuenta_id)->first();
            $this->addXp($user, 'complete_meta');
        }
    }

    /**
     * Handle the Meta "deleted" event.
     */
    public function deleted(Meta $meta): void
    {
        //
    }

    /**
     * Handle the Meta "restored" event.
     */
    public function restored(Meta $meta): void
    {
        //
    }

    /**
     * Handle the Meta "force deleted" event.
     */
    public function forceDeleted(Meta $meta): void
    {
        //
    }
}
