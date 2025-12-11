<?php

namespace Tests\Feature;

use App\Models\Elementos\Alarma;
use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlarmaControllerTest extends TestCase
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
    public function it_can_list_user_alarms()
    {
        // Crear alarmas para el usuario
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);

        Alarma::factory()->create(['elemento_id' => $elemento1->id]);
        Alarma::factory()->create(['elemento_id' => $elemento2->id]);

        // Crear alarma de otro usuario (no debe aparecer)
        $otherUser = User::factory()->create();
        $otherCuenta = UsuarioCuenta::factory()->create(['user_id' => $otherUser->id]);
        $otherElemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $otherCuenta->id
        ]);
        Alarma::factory()->create(['elemento_id' => $otherElemento->id]);

        $response = $this->getJson('/api/alarms');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_can_create_a_new_alarm()
    {
        $alarmData = [
            'nombre' => 'Alarm Test',
            'informacion' => 'Test alarm description',
            'fecha' => '2024-12-25',
            'hora' => '08:00:00',
            'intensidad_volumen' => 5,
            'configuraciones' => [
                'gps' => [
                    'latitude' => -34.6037,
                    'longitude' => -58.3816,
                    'radius' => 100
                ],
                'clima' => [
                    'enabled' => true,
                    'conditions' => ['rain', 'storm']
                ]
            ]
        ];

        $response = $this->postJson('/api/alarms', $alarmData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nombre',
                        'informacion',
                        'fecha',
                        'hora',
                        'elemento' => ['id', 'tipo', 'estado']
                    ]
                ]);

        $this->assertDatabaseHas('alarmas', [
            'nombre' => 'Alarm Test',
            'informacion' => 'Test alarm description'
        ]);

        $this->assertDatabaseHas('elementos', [
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
    }

    /** @test */
    public function it_validates_alarm_creation_data()
    {
        // Test sin nombre
        $response = $this->postJson('/api/alarms', [
            'informacion' => 'Test',
            'fecha' => '2024-12-25',
            'hora' => '08:00:00'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nombre']);

        // Test con fecha inválida
        $response = $this->postJson('/api/alarms', [
            'nombre' => 'Test Alarm',
            'fecha' => 'invalid-date',
            'hora' => '08:00:00'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['fecha']);

        // Test con hora inválida
        $response = $this->postJson('/api/alarms', [
            'nombre' => 'Test Alarm',
            'fecha' => '2024-12-25',
            'hora' => '25:00:00'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['hora']);
    }

    /** @test */
    public function it_can_show_a_specific_alarm()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        $response = $this->getJson("/api/alarms/{$alarma->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nombre',
                        'informacion',
                        'fecha',
                        'hora',
                        'elemento'
                    ]
                ]);
    }

    /** @test */
    public function it_cannot_show_alarm_from_another_user()
    {
        $otherUser = User::factory()->create();
        $otherCuenta = UsuarioCuenta::factory()->create(['user_id' => $otherUser->id]);
        $otherElemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $otherCuenta->id
        ]);
        $otherAlarma = Alarma::factory()->create(['elemento_id' => $otherElemento->id]);

        $response = $this->getJson("/api/alarms/{$otherAlarma->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_an_alarm()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        $updateData = [
            'nombre' => 'Updated Alarm Name',
            'informacion' => 'Updated description',
            'intensidad_volumen' => 8
        ];

        $response = $this->putJson("/api/alarms/{$alarma->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('alarmas', [
            'id' => $alarma->id,
            'nombre' => 'Updated Alarm Name',
            'informacion' => 'Updated description',
            'intensidad_volumen' => 8
        ]);
    }

    /** @test */
    public function it_can_delete_an_alarm()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        $response = $this->deleteJson("/api/alarms/{$alarma->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('alarmas', ['id' => $alarma->id]);
        $this->assertSoftDeleted('elementos', ['id' => $elemento->id]);
    }

    /** @test */
    public function it_can_toggle_alarm_status()
    {
        $elemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'estado' => 'activo',
            'cuenta_id' => $this->cuenta->id
        ]);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        $response = $this->patchJson("/api/alarms/{$alarma->id}/toggle");

        $response->assertStatus(200);

        $this->assertDatabaseHas('elementos', [
            'id' => $elemento->id,
            'estado' => 'inactivo'
        ]);

        // Toggle again
        $response = $this->patchJson("/api/alarms/{$alarma->id}/toggle");

        $response->assertStatus(200);

        $this->assertDatabaseHas('elementos', [
            'id' => $elemento->id,
            'estado' => 'activo'
        ]);
    }

    /** @test */
    public function it_handles_gps_configuration_correctly()
    {
        $alarmData = [
            'nombre' => 'GPS Alarm',
            'informacion' => 'GPS-enabled alarm',
            'fecha' => '2024-12-25',
            'hora' => '08:00:00',
            'configuraciones' => [
                'gps' => [
                    'latitude' => -34.6037,
                    'longitude' => -58.3816,
                    'radius' => 500,
                    'type' => 'enter'
                ]
            ]
        ];

        $response = $this->postJson('/api/alarms', $alarmData);

        $response->assertStatus(201);

        $alarma = Alarma::latest()->first();
        $this->assertNotNull($alarma->gps);
        $this->assertEquals(-34.6037, $alarma->gps['latitude']);
        $this->assertEquals(-58.3816, $alarma->gps['longitude']);
        $this->assertEquals(500, $alarma->gps['radius']);
    }

    /** @test */
    public function it_handles_weather_configuration_correctly()
    {
        $alarmData = [
            'nombre' => 'Weather Alarm',
            'informacion' => 'Weather-dependent alarm',
            'fecha' => '2024-12-25',
            'hora' => '08:00:00',
            'configuraciones' => [
                'clima' => [
                    'enabled' => true,
                    'conditions' => ['rain', 'storm'],
                    'temperature_min' => 15,
                    'temperature_max' => 25
                ]
            ]
        ];

        $response = $this->postJson('/api/alarms', $alarmData);

        $response->assertStatus(201);

        $alarma = Alarma::latest()->first();
        $this->assertNotNull($alarma->clima);
        $this->assertTrue($alarma->clima['enabled']);
        $this->assertContains('rain', $alarma->clima['conditions']);
        $this->assertContains('storm', $alarma->clima['conditions']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Limpiar la autenticación
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/alarms');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_filters_alarms_by_date_range()
    {
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);

        Alarma::factory()->create([
            'elemento_id' => $elemento1->id,
            'fecha' => '2024-12-25'
        ]);
        Alarma::factory()->create([
            'elemento_id' => $elemento2->id,
            'fecha' => '2025-01-15'
        ]);

        $response = $this->getJson('/api/alarms?fecha_inicio=2024-12-01&fecha_fin=2024-12-31');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_searches_alarms_by_name()
    {
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $this->cuenta->id
        ]);

        Alarma::factory()->create([
            'elemento_id' => $elemento1->id,
            'nombre' => 'Trabajo Meeting'
        ]);
        Alarma::factory()->create([
            'elemento_id' => $elemento2->id,
            'nombre' => 'Personal Reminder'
        ]);

        $response = $this->getJson('/api/alarms?search=trabajo');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }
}