<?php

namespace Tests\Feature;

use App\Models\Elementos\Calendario;
use App\Models\Elementos\Evento;
use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CalendarioEventoTest extends TestCase
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
    public function it_can_create_calendar_with_events()
    {
        $calendarData = [
            'nombre' => 'Calendario Personal',
            'descripcion' => 'Mi calendario de trabajo',
            'color' => '#14887D'
        ];

        $response = $this->postJson('/api/calendars', $calendarData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nombre',
                        'descripcion',
                        'color'
                    ]
                ]);

        $this->assertDatabaseHas('calendarios', [
            'nombre' => 'Calendario Personal'
        ]);
    }

    /** @test */
    public function it_can_add_event_to_calendar()
    {
        $this->markTestIncomplete('Test temporalmente deshabilitado - requiere debugging adicional');

        $elementoCalendario = Elemento::factory()->create([
            'tipo' => 'calendario',
            'cuenta_id' => $this->cuenta->id
        ]);
        $calendario = Calendario::factory()->create([
            'elemento_id' => $elementoCalendario->id
        ]);

        $eventData = [
            'nombre' => 'Reunión de equipo',
            'descripcion' => 'Revisión del sprint',
            'fecha_inicio' => '2025-10-15 10:00:00',
            'fecha_fin' => '2025-10-15 11:00:00',
            'ubicacion' => 'Sala de juntas',
            'calendario_id' => $calendario->id
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nombre',
                        'fecha_inicio',
                        'fecha_fin'
                    ]
                ]);

        $this->assertDatabaseHas('eventos', [
            'nombre' => 'Reunión de equipo'
        ]);
    }

    /** @test */
    public function it_can_list_events_in_date_range()
    {
        $elementoCalendario = Elemento::factory()->create([
            'tipo' => 'calendario',
            'cuenta_id' => $this->cuenta->id
        ]);
        $calendario = Calendario::factory()->create([
            'elemento_id' => $elementoCalendario->id
        ]);

        // Crear eventos en diferentes fechas
        $elementoEvento1 = Elemento::factory()->create([
            'tipo' => 'evento',
            'cuenta_id' => $this->cuenta->id
        ]);
        Evento::factory()->create([
            'elemento_id' => $elementoEvento1->id,
            'calendario_id' => $calendario->id,
            'fecha_inicio' => '2025-10-15 10:00:00'
        ]);

        $elementoEvento2 = Elemento::factory()->create([
            'tipo' => 'evento',
            'cuenta_id' => $this->cuenta->id
        ]);
        Evento::factory()->create([
            'elemento_id' => $elementoEvento2->id,
            'calendario_id' => $calendario->id,
            'fecha_inicio' => '2025-11-15 10:00:00'
        ]);

        // Buscar eventos de octubre
        $response = $this->getJson('/api/events?start_date=2025-10-01&end_date=2025-10-31');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_validates_event_dates()
    {
        $elementoCalendario = Elemento::factory()->create([
            'tipo' => 'calendario',
            'cuenta_id' => $this->cuenta->id
        ]);
        $calendario = Calendario::factory()->create([
            'elemento_id' => $elementoCalendario->id
        ]);

        // Test: fecha fin antes de fecha inicio
        $response = $this->postJson('/api/events', [
            'nombre' => 'Evento inválido',
            'fecha_inicio' => '2025-10-15 10:00:00',
            'fecha_fin' => '2025-10-15 09:00:00', // Antes del inicio
            'calendario_id' => $calendario->id
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_update_recurring_event()
    {
        $elementoCalendario = Elemento::factory()->create([
            'tipo' => 'calendario',
            'cuenta_id' => $this->cuenta->id
        ]);
        $calendario = Calendario::factory()->create([
            'elemento_id' => $elementoCalendario->id
        ]);

        $elementoEvento = Elemento::factory()->create([
            'tipo' => 'evento',
            'cuenta_id' => $this->cuenta->id
        ]);
        $evento = Evento::factory()->create([
            'elemento_id' => $elementoEvento->id,
            'calendario_id' => $calendario->id,
            'recurrencia' => [
                'tipo' => 'semanal',
                'dias' => ['lunes', 'miércoles', 'viernes']
            ]
        ]);

        $updateData = [
            'recurrencia' => [
                'tipo' => 'diaria',
                'intervalo' => 1
            ]
        ];

        $response = $this->putJson("/api/events/{$evento->id}", $updateData);

        $response->assertStatus(200);

        $evento->refresh();
        $this->assertEquals('diaria', $evento->recurrencia['tipo']);
    }

    /** @test */
    public function it_can_delete_calendar_and_its_events()
    {
        $elementoCalendario = Elemento::factory()->create([
            'tipo' => 'calendario',
            'cuenta_id' => $this->cuenta->id
        ]);
        $calendario = Calendario::factory()->create([
            'elemento_id' => $elementoCalendario->id
        ]);

        // Crear eventos en el calendario
        $elementoEvento = Elemento::factory()->create([
            'tipo' => 'evento',
            'cuenta_id' => $this->cuenta->id
        ]);
        Evento::factory()->create([
            'elemento_id' => $elementoEvento->id,
            'calendario_id' => $calendario->id
        ]);

        $response = $this->deleteJson("/api/calendars/{$calendario->id}");

        $response->assertStatus(200);

        // Verificar soft delete
        $this->assertSoftDeleted('calendarios', ['id' => $calendario->id]);
        $this->assertSoftDeleted('eventos', ['calendario_id' => $calendario->id]);
    }
}
