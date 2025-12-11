<?php

namespace Tests\Feature;

use App\Models\Elementos\Nota;
use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\SeedsTiposNotas;

class NotaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, SeedsTiposNotas;

    protected $user;
    protected $cuenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTiposNotas();

        $this->user = User::factory()->create();
        $this->cuenta = UsuarioCuenta::factory()->create(['user_id' => $this->user->id]);
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_user_notes()
    {
        // Crear notas para el usuario
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);

        Nota::factory()->create(['elemento_id' => $elemento1->id]);
        Nota::factory()->create(['elemento_id' => $elemento2->id]);

        // Crear nota de otro usuario (no debe aparecer)
        $otherUser = User::factory()->create();
        $otherCuenta = UsuarioCuenta::factory()->create(['user_id' => $otherUser->id]);
        $otherElemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $otherCuenta->id
        ]);
        Nota::factory()->create(['elemento_id' => $otherElemento->id]);

        $response = $this->getJson('/api/notes');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_create_a_new_note()
    {
        $noteData = [
            'nombre' => 'My First Note',
            'informacion' => 'This is a test note',
            'contenido' => [
                'text' => 'This is the note content',
                'formatting' => ['bold', 'italic'],
                'tags' => ['important', 'work']
            ],
            'fecha' => '2024-12-25',
            'clave' => 'secret123'
        ];

        $response = $this->postJson('/api/notes', $noteData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nombre',
                        'informacion',
                        'contenido',
                        'fecha',
                        'elemento' => ['id', 'tipo', 'estado']
                    ]
                ]);

        $this->assertDatabaseHas('notas', [
            'nombre' => 'My First Note',
            'informacion' => 'This is a test note',
            'clave' => 'secret123'
        ]);

        $this->assertDatabaseHas('elementos', [
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
    }

    /** @test */
    public function it_validates_note_creation_data()
    {
        // Test sin nombre
        $response = $this->postJson('/api/notes', [
            'informacion' => 'Test note',
            'contenido' => ['text' => 'content']
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nombre']);

        // Test con fecha inválida
        $response = $this->postJson('/api/notes', [
            'nombre' => 'Test Note',
            'fecha' => 'invalid-date'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['fecha']);
    }

    /** @test */
    public function it_can_show_a_specific_note()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        $response = $this->getJson("/api/notes/{$nota->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nombre',
                        'informacion',
                        'contenido',
                        'fecha',
                        'elemento'
                    ]
                ]);
    }

    /** @test */
    public function it_cannot_show_note_from_another_user()
    {
        $otherUser = User::factory()->create();
        $otherCuenta = UsuarioCuenta::factory()->create(['user_id' => $otherUser->id]);
        $otherElemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $otherCuenta->id
        ]);
        $otherNota = Nota::factory()->create(['elemento_id' => $otherElemento->id]);

        $response = $this->getJson("/api/notes/{$otherNota->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_note()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        $updateData = [
            'nombre' => 'Updated Note Name',
            'informacion' => 'Updated description',
            'contenido' => [
                'text' => 'Updated content',
                'tags' => ['updated', 'new']
            ]
        ];

        $response = $this->putJson("/api/notes/{$nota->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notas', [
            'id' => $nota->id,
            'nombre' => 'Updated Note Name',
            'informacion' => 'Updated description'
        ]);
    }

    /** @test */
    public function it_can_delete_a_note()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        $response = $this->deleteJson("/api/notes/{$nota->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('notas', ['id' => $nota->id]);
        $this->assertSoftDeleted('elementos', ['id' => $elemento->id]);
    }

    /** @test */
    public function it_can_search_notes_by_content()
    {
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);

        Nota::factory()->create([
            'elemento_id' => $elemento1->id,
            'nombre' => 'Work Meeting Notes',
            'informacion' => 'Important work discussions'
        ]);
        Nota::factory()->create([
            'elemento_id' => $elemento2->id,
            'nombre' => 'Personal Thoughts',
            'informacion' => 'Random personal ideas'
        ]);

        $response = $this->getJson('/api/notes?search=work');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_notes_by_date_range()
    {
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);

        Nota::factory()->create([
            'elemento_id' => $elemento1->id,
            'fecha' => '2024-12-15'
        ]);
        Nota::factory()->create([
            'elemento_id' => $elemento2->id,
            'fecha' => '2025-01-15'
        ]);

        $response = $this->getJson('/api/notes?fecha_inicio=2024-12-01&fecha_fin=2024-12-31');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_notes_by_tags()
    {
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);

        Nota::factory()->create([
            'elemento_id' => $elemento1->id,
            'contenido' => ['tags' => ['work', 'important']]
        ]);
        Nota::factory()->create([
            'elemento_id' => $elemento2->id,
            'contenido' => ['tags' => ['personal', 'ideas']]
        ]);

        $response = $this->getJson('/api/notes?tags=work');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_sort_notes_by_different_criteria()
    {
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);

        Nota::factory()->create([
            'elemento_id' => $elemento1->id,
            'nombre' => 'Alpha Note',
            'fecha' => '2024-12-15'
        ]);
        Nota::factory()->create([
            'elemento_id' => $elemento2->id,
            'nombre' => 'Beta Note',
            'fecha' => '2024-12-10'
        ]);

        // Sort by name ascending
        $response = $this->getJson('/api/notes?sort_by=nombre&sort_order=asc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Alpha Note', $data[0]['nombre']);

        // Sort by date descending
        $response = $this->getJson('/api/notes?sort_by=fecha&sort_order=desc');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('2024-12-15', $data[0]['fecha']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Create a fresh test instance without authentication
        $this->refreshApplication();

        $response = $this->getJson('/api/notes');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_encrypted_notes()
    {
        $noteData = [
            'nombre' => 'Secret Note',
            'informacion' => 'This is an encrypted note',
            'contenido' => [
                'text' => 'Secret information',
                'encrypted' => true
            ],
            'clave' => 'encryption_key_123'
        ];

        $response = $this->postJson('/api/notes', $noteData);

        $response->assertStatus(201);

        $nota = Nota::latest()->first();
        $this->assertEquals('encryption_key_123', $nota->clave);
        $this->assertTrue($nota->contenido['encrypted']);
    }

    /** @test */
    public function it_can_duplicate_a_note()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $this->cuenta->id
        ]);
        $nota = Nota::factory()->create([
            'elemento_id' => $elemento->id,
            'nombre' => 'Original Note'
        ]);

        $response = $this->postJson("/api/notes/{$nota->id}/duplicate");

        $response->assertStatus(201);

        $this->assertDatabaseHas('notas', [
            'nombre' => 'Original Note (Copy)'
        ]);

        // Should have 2 notes now
        $this->assertEquals(2, Nota::count());
    }

    /** @test */
    public function it_can_archive_and_unarchive_notes()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'estado' => 'activo',
            'cuenta_id' => $this->cuenta->id
        ]);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        // Archive note
        $response = $this->patchJson("/api/notes/{$nota->id}/archive");

        $response->assertStatus(200);

        $this->assertDatabaseHas('elementos', [
            'id' => $elemento->id,
            'estado' => 'archivado'
        ]);

        // Unarchive note
        $response = $this->patchJson("/api/notes/{$nota->id}/unarchive");

        $response->assertStatus(200);

        $this->assertDatabaseHas('elementos', [
            'id' => $elemento->id,
            'estado' => 'activo'
        ]);
    }
}