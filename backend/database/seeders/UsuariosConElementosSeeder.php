<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Nota;
use App\Models\Elementos\Alarma;
use App\Models\Elementos\Evento;
use App\Models\Elementos\Calendario;
use App\Models\Elementos\Meta;
use App\Models\Elementos\Objetivo;
use Illuminate\Support\Facades\DB;


class UsuariosConElementosSeeder extends Seeder
{
    public function run()
    {
        
        // Crea 5 usuarios con su cuenta asociada
        User::factory()
            ->count(5)
            ->create()
            ->each(function ($user) {
                // Crea la cuenta asociada
                $cuenta = UsuarioCuenta::factory()->create([
                    'user_id' => $user->id,
                ]);

                // Crea 10 elementos distintos para cada cuenta
                $tipos = ['nota', 'alarma', 'calendario','objetivo', 'nota', 'alarma', 'calendario'];
                foreach ($tipos as $tipo) {
                    $elemento = Elemento::factory()->create([
                        'cuenta_id' => $cuenta->id,
                        'tipo' => $tipo,
                    ]);
                    switch ($tipo) {
                        case 'nota':
                            Nota::factory()->create([
                                'elemento_id' => $elemento->id,
                            ]);
                            break;
                        case 'alarma':
                            Alarma::factory()->create([
                                'elemento_id' => $elemento->id,
                            ]);
                            break;
                        case 'calendario':
                            $calendario = Calendario::factory()->create([
                                'elemento_id' => $elemento->id,
                            ]);
                            // Crea 2 eventos para cada calendario
                            Evento::factory(2)->create([
                                'elemento_id' => $elemento->id,
                                'calendario_id' => $calendario->id,
                            ]);
                            break;
                        case 'objetivo':
                            $objetivo = Objetivo::factory()->create([
                                'elemento_id' => $elemento->id,
                            ]);
                            // Crea 2 metas para cada objetivo
                            Meta::factory(2)->create([
                                'elemento_id' => $elemento->id,
                                'objetivo_id' => $objetivo->id,
                            ]);
                            break;
                    }
                }
            });
    }
}