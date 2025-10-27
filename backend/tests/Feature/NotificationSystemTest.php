<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsuarioCuenta;
use App\Models\Notificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $cuenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cuenta = UsuarioCuenta::factory()->create(['user_id' => $this->user->id]);
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_user_notifications()
    {
        // Crear notificaciones para el usuario
        Notificacion::factory()->count(3)->create([
            'cuenta_id' => $this->cuenta->id,
            'leido' => false
        ]);

        // Crear notificación de otro usuario (no debe aparecer)
        $otherUser = User::factory()->create();
        $otherCuenta = UsuarioCuenta::factory()->create(['user_id' => $otherUser->id]);
        Notificacion::factory()->create([
            'cuenta_id' => $otherCuenta->id
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_mark_notification_as_read()
    {
        $notificacion = Notificacion::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'leido' => false
        ]);

        $response = $this->patchJson("/api/notifications/{$notificacion->id}/read");

        $response->assertStatus(200);

        $this->assertDatabaseHas('notificaciones', [
            'id' => $notificacion->id,
            'leido' => true
        ]);
    }

    /** @test */
    public function it_can_mark_all_notifications_as_read()
    {
        Notificacion::factory()->count(5)->create([
            'cuenta_id' => $this->cuenta->id,
            'leido' => false
        ]);

        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertStatus(200);

        $unreadCount = Notificacion::where('cuenta_id', $this->cuenta->id)
                                   ->where('leido', false)
                                   ->count();

        $this->assertEquals(0, $unreadCount);
    }

    /** @test */
    public function it_can_get_unread_notifications_count()
    {
        Notificacion::factory()->count(3)->create([
            'cuenta_id' => $this->cuenta->id,
            'leido' => false
        ]);

        Notificacion::factory()->count(2)->create([
            'cuenta_id' => $this->cuenta->id,
            'leido' => true
        ]);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
                ->assertJson(['count' => 3]);
    }

    /** @test */
    public function it_creates_notification_when_user_levels_up()
    {
        // Actualizar nivel del usuario
        $this->cuenta->update(['nivel' => 2]);

        // Verificar que se creó una notificación de nivel
        $this->assertDatabaseHas('notificaciones', [
            'cuenta_id' => $this->cuenta->id,
            'tipo' => 'nivel_up'
        ]);
    }

    /** @test */
    public function it_can_delete_notification()
    {
        $notificacion = Notificacion::factory()->create([
            'cuenta_id' => $this->cuenta->id
        ]);

        $response = $this->deleteJson("/api/notifications/{$notificacion->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('notificaciones', [
            'id' => $notificacion->id
        ]);
    }

    /** @test */
    public function it_filters_notifications_by_type()
    {
        Notificacion::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'tipo' => 'nivel_up'
        ]);

        Notificacion::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'tipo' => 'alarma'
        ]);

        Notificacion::factory()->create([
            'cuenta_id' => $this->cuenta->id,
            'tipo' => 'sistema'
        ]);

        $response = $this->getJson('/api/notifications?tipo=nivel_up');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }
}
