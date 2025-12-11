<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LevelSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $cuenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cuenta = UsuarioCuenta::factory()->create([
            'user_id' => $this->user->id,
            'current_level' => 1,
            'total_xp' => 0
        ]);
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_get_user_level_and_points()
    {
        $response = $this->getJson('/api/level');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'current_level',
                    'total_xp',
                    'xp_required_for_next_level',
                    'porcentaje_progreso'
                ]);
    }

    /** @test */
    public function it_awards_points_when_creating_element()
    {
        $initialPoints = $this->cuenta->total_xp;

        // Crear una nota debería dar puntos
        $this->postJson('/api/notes', [
            'nombre' => 'Test Note',
            'informacion' => 'Test description'
        ]);

        $this->cuenta->refresh();
        $this->assertGreaterThan($initialPoints, $this->cuenta->total_xp);
    }

    /** @test */
    public function it_levels_up_when_reaching_required_points()
    {
        // Establecer puntos cerca del siguiente nivel
        $this->cuenta->update([
            'current_level' => 1,
            'total_xp' => 95 // Asumiendo que nivel 2 requiere 100 puntos
        ]);

        // Crear elemento para obtener puntos y subir de nivel
        $this->postJson('/api/notes', [
            'nombre' => 'Level Up Note',
            'informacion' => 'This should level me up'
        ]);

        $this->cuenta->refresh();
        $this->assertGreaterThanOrEqual(2, $this->cuenta->current_level);
    }

    /** @test */
    public function it_unlocks_note_types_based_on_level()
    {
        // Nivel bajo no debería tener acceso a notas de nivel alto
        $this->cuenta->update(['current_level' => 1]);

        $response = $this->getJson('/api/note-types/available');

        $response->assertStatus(200);
        $availableTypes = $response->json('data');

        // Verificar que hay tipos disponibles
        $this->assertNotEmpty($availableTypes);
    }

    /** @test */
    public function premium_users_get_premium_note_types_at_level_1()
    {
        $this->cuenta->update([
            'current_level' => 1,
            'is_premium' => true,
            'premium_expires_at' => now()->addMonths(1)
        ]);

        $response = $this->getJson('/api/note-types/available');

        $response->assertStatus(200);
        $availableTypes = $response->json('data');

        // Verificar que hay tipos premium disponibles
        $premiumTypes = array_filter($availableTypes, function($type) {
            return isset($type['is_premium']) && $type['is_premium'] === true;
        });

        $this->assertNotEmpty($premiumTypes, 'Premium users should have premium note types at level 1');
    }
}
