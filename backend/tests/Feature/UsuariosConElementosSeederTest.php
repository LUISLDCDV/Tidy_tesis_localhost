<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsuariosConElementosSeeder;
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

class UsuariosConElementosSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuarios_con_elementos_seeder()
    {
        DB::table('tipos_notas')->insert(['id' => 1, 'nombre' => 'General']);
        // Ejecuta el seeder
        $this->seed(UsuariosConElementosSeeder::class);

        // Verifica que hay 5 usuarios y 5 cuentas
        $this->assertEquals(5, User::count());
        $this->assertEquals(5, UsuarioCuenta::count());

        // Verifica que cada usuario tiene 7 elementos (según tu array de tipos)
        $this->assertEquals(35, Elemento::count());

        // Verifica que hay notas, alarmas, calendarios, objetivos, eventos y metas
        $this->assertTrue(Nota::count() > 0);
        $this->assertTrue(Alarma::count() > 0);
        $this->assertTrue(Calendario::count() > 0);
        $this->assertTrue(Evento::count() > 0);
        $this->assertTrue(Objetivo::count() > 0);
        $this->assertTrue(Meta::count() > 0);
    }
}