<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Nota;
use App\Models\Elementos\Alarma;
use App\Models\Elementos\Objetivo;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ElementoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_elemento()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        
        $elemento = Elemento::create([
            'tipo' => 'nota',
            'descripcion' => 'Test elemento',
            'estado' => 'activo',
            'orden' => 1,
            'cuenta_id' => $cuenta->id
        ]);

        $this->assertInstanceOf(Elemento::class, $elemento);
        $this->assertEquals('nota', $elemento->tipo);
        $this->assertEquals('Test elemento', $elemento->descripcion);
    }

    /** @test */
    public function it_has_relationship_with_usuario_cuenta()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        
        $elemento = Elemento::factory()->create([
            'cuenta_id' => $cuenta->id
        ]);

        $this->assertInstanceOf(UsuarioCuenta::class, $elemento->usuario);
        $this->assertEquals($cuenta->id, $elemento->usuario->id);
    }

    /** @test */
    public function it_has_relationship_with_nota()
    {
        $elemento = Elemento::factory()->create(['tipo' => 'nota']);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        $this->assertInstanceOf(Nota::class, $elemento->nota);
        $this->assertEquals($nota->id, $elemento->nota->id);
    }

    /** @test */
    public function it_has_relationship_with_alarma()
    {
        $elemento = Elemento::factory()->create(['tipo' => 'alarma']);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        $this->assertInstanceOf(Alarma::class, $elemento->alarma);
        $this->assertEquals($alarma->id, $elemento->alarma->id);
    }

    /** @test */
    public function it_has_relationship_with_objetivo()
    {
        $elemento = Elemento::factory()->create(['tipo' => 'objetivo']);
        $objetivo = Objetivo::factory()->create(['elemento_id' => $elemento->id]);

        $this->assertInstanceOf(Objetivo::class, $elemento->objetivo);
        $this->assertEquals($objetivo->id, $elemento->objetivo->id);
    }

    /** @test */
    public function it_can_update_element_content()
    {
        $elemento = Elemento::factory()->create([
            'contenido' => 'Original content'
        ]);

        $elemento->actualizarElemento('New content');

        $this->assertEquals('New content', $elemento->fresh()->contenido);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $elemento = new Elemento();
        
        $expectedFillable = [
            'tipo',
            'descripcion',
            'estado',
            'imagen',
            'orden'
        ];

        $this->assertEquals($expectedFillable, $elemento->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $elemento = new Elemento();
        
        $this->assertEquals('elementos', $elemento->getTable());
    }

    /** @test */
    public function it_uses_correct_primary_key()
    {
        $elemento = new Elemento();
        
        $this->assertEquals('id', $elemento->getKeyName());
    }
}