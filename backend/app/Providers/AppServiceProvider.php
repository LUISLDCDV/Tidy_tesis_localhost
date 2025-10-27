<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\NoteObserver;
use  App\Models\Elementos\Nota;
use App\Observers\EventObserver;
use App\Models\Elementos\Evento;
use App\Observers\AlarmObserver;
use App\Models\Elementos\Alarma;
use App\Observers\ObjetivoObserver;
use App\Models\Elementos\Meta;
use App\Observers\CalendarEntryObserver;
use App\Models\Elementos\EntradaCalendario;
use App\Models\Elementos\Calendario;
use App\Models\Elementos\Objetivo;
use App\Observers\MetaObserver;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;
use App\Observers\ElementoGamificationObserver;

use App\Traits\HandlesUserXP;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (env('FORCE_HTTPS', false)) {
            \URL::forceScheme('https');
        }

        Nota::observe(NoteObserver::class);
        Evento::observe(EventObserver::class);
        Alarma::observe(AlarmObserver::class);
        Meta::observe(MetaObserver::class);
        Objetivo::observe(ObjetivoObserver::class);
        Calendario::observe(CalendarEntryObserver::class);

        // Observer para gamificación de elementos
        Elemento::observe(ElementoGamificationObserver::class);
    }
}
