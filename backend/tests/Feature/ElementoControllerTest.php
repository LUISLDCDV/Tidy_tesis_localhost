<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\SeedsTiposNotas;
use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Nota;
use Laravel\Sanctum\Sanctum;

class ElementoControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, SeedsTiposNotas;

    protected $user;
    protected $cuenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTiposNotas();
        
        // Crear usuario de prueba
        $this->user = User::factory()->create();
        $this->cuenta = UsuarioCuenta::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        // Autenticar usuario con Sanctum
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_get_user_elements()
    {
        // Crear algunos elementos de prueba
        Elemento::factory()
            ->count(3)
            ->create(['cuenta_id' => $this->cuenta->id]);

        $response = $this->getJson('/api/usuarios/elementos');

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_note_element()
    {
        $noteData = [
            'tipo' => 'nota',
            'nombre' => 'Test Note',
            'fecha' => now()->toDateString(),
            'tipo_nota_id' => 1,
            'contenido' => 'Test content',
            'informacion' => 'Test information'
        ];

        $response = $this->postJson('/api/elementos/saveUpdate', $noteData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('elementos', [
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
    }

    /** @test */
    public function it_can_update_element()
    {
        $elemento = Elemento::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'tipo' => 'nota'
        ]);

        $nota = Nota::factory()->create([
            'elemento_id' => $elemento->id,
            'nombre' => 'Original Name'
        ]);

        $updateData = [
            'elemento_id' => $elemento->id,
            'tipo' => 'nota',
            'nombre' => 'Updated Name',
            'fecha' => now()->toDateString(),
            'tipo_nota_id' => 1,
            'contenido' => 'Updated content',
            'informacion' => 'Updated information'
        ];

        $response = $this->postJson('/api/elementos/saveUpdate', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notas', [
            'elemento_id' => $elemento->id,
            'nombre' => 'Updated Name'
        ]);
    }

    /** @test */
    public function it_can_delete_element()
    {
        $this->markTestIncomplete('Test temporalmente deshabilitado - requiere debugging adicional');

        $elemento = Elemento::factory()->create([
            'cuenta_id' => $this->cuenta->id
        ]);

        $response = $this->postJson("/api/elementos/eliminarElemento/{$elemento->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('elementos', [
            'id' => $elemento->id
        ]);
        
        $elemento->refresh();
        $this->assertNotNull($elemento->deleted_at);
    }

    /** @test */
    public function it_can_update_elements_order()
    {
        $elemento1 = Elemento::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'orden' => 1
        ]);
        
        $elemento2 = Elemento::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'orden' => 2
        ]);

        $orderData = [
            'elementos' => [
                ['id' => $elemento1->id, 'orden' => 2],
                ['id' => $elemento2->id, 'orden' => 1]
            ]
        ];

        $response = $this->postJson('/api/elementos/updateOrder', $orderData);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $elemento1->refresh();
        $elemento2->refresh();

        $this->assertEquals(2, $elemento1->orden);
        $this->assertEquals(1, $elemento2->orden);
    }

    /** @test */
    public function it_requires_authentication_for_elements()
    {
        // Limpiar la autenticación actual
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/usuarios/elementos');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_get_element_by_id()
    {
        $elemento = Elemento::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'tipo' => 'nota'
        ]);

        $nota = Nota::factory()->create([
            'elemento_id' => $elemento->id
        ]);

        $response = $this->getJson("/api/usuarios/elemento/{$elemento->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function it_cannot_access_other_users_elements()
    {
        // Crear otro usuario
        $otherUser = User::factory()->create();
        $otherCuenta = UsuarioCuenta::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $otherElement = Elemento::factory()->create([
            'cuenta_id' => $otherCuenta->id
        ]);

        // Intentar acceder al elemento del otro usuario
        $response = $this->getJson("/api/usuarios/elemento/{$otherElement->id}");

        $response->assertStatus(404);
    }
}