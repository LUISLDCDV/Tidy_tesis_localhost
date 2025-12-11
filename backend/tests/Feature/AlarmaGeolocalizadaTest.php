<?php
// TODO: Desactivar temporalmente - Pruebas de geolocalización hasta mvp
namespace Tests\Feature;

use App\Models\Elementos\Alarma;
use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlarmaGeolocalizadaTest extends TestCase
{
//     use RefreshDatabase, WithFaker;

//     protected $user;
//     protected $cuenta;

//     protected function setUp(): void
//     {
//         parent::setUp();

//         $this->user = User::factory()->create();
//         $this->cuenta = UsuarioCuenta::factory()->create(['user_id' => $this->user->id]);
//         Sanctum::actingAs($this->user);
//     }

//     /** @test */
//     public function it_can_create_geolocation_alarm()
//     {
//         $alarmData = [
//             'nombre' => 'Comprar en el supermercado',
//             'descripcion' => 'Recordar comprar leche',
//             'ubicacion' => [
//                 'lat' => -34.6037,
//                 'lng' => -58.3816,
//                 'address' => 'Buenos Aires, Argentina',
//                 'radius' => 500
//             ],
//             'tipo_alarma' => 'geolocalizada'
//         ];

//         $response = $this->postJson('/api/alarms', $alarmData);

//         $response->assertStatus(201)
//                 ->assertJsonStructure([
//                     'data' => [
//                         'id',
//                         'nombre',
//                         'descripcion',
//                         'ubicacion'
//                     ]
//                 ]);

//         $this->assertDatabaseHas('alarmas', [
//             'nombre' => 'Comprar en el supermercado',
//             'tipo_alarma' => 'geolocalizada'
//         ]);
//     }

//     /** @test */
//     public function it_validates_geolocation_data()
//     {
//         // Test sin coordenadas
//         $response = $this->postJson('/api/alarms', [
//             'nombre' => 'Test Alarm',
//             'tipo_alarma' => 'geolocalizada',
//             'ubicacion' => []
//         ]);

//         $response->assertStatus(422);

//         // Test con coordenadas inválidas
//         $response = $this->postJson('/api/alarms', [
//             'nombre' => 'Test Alarm',
//             'tipo_alarma' => 'geolocalizada',
//             'ubicacion' => [
//                 'lat' => 999, // Latitud inválida
//                 'lng' => 999  // Longitud inválida
//             ]
//         ]);

//         $response->assertStatus(422);
//     }

//     /** @test */
//     public function it_can_list_nearby_alarms()
//     {
//         // Crear varias alarmas geolocalizadas
//         $elemento1 = Elemento::factory()->create([
//             'tipo' => 'alarma',
//             'cuenta_id' => $this->cuenta->id
//         ]);
//         Alarma::factory()->create([
//             'elemento_id' => $elemento1->id,
//             'tipo_alarma' => 'geolocalizada',
//             'ubicacion' => [
//                 'lat' => -34.6037,
//                 'lng' => -58.3816
//             ]
//         ]);

//         $elemento2 = Elemento::factory()->create([
//             'tipo' => 'alarma',
//             'cuenta_id' => $this->cuenta->id
//         ]);
//         Alarma::factory()->create([
//             'elemento_id' => $elemento2->id,
//             'tipo_alarma' => 'geolocalizada',
//             'ubicacion' => [
//                 'lat' => -34.7037,
//                 'lng' => -58.4816
//             ]
//         ]);

//         // Buscar alarmas cerca de una ubicación
//         $response = $this->getJson('/api/alarms/nearby?lat=-34.6037&lng=-58.3816&radius=10000');

//         $response->assertStatus(200)
//                 ->assertJsonStructure([
//                     'data' => [
//                         '*' => ['id', 'nombre', 'ubicacion']
//                     ]
//                 ]);
//     }

//     /** @test */
//     public function it_can_update_alarm_location()
//     {
//         $elemento = Elemento::factory()->create([
//             'tipo' => 'alarma',
//             'cuenta_id' => $this->cuenta->id
//         ]);
//         $alarma = Alarma::factory()->create([
//             'elemento_id' => $elemento->id,
//             'tipo_alarma' => 'geolocalizada',
//             'ubicacion' => [
//                 'lat' => -34.6037,
//                 'lng' => -58.3816
//             ]
//         ]);

//         $updateData = [
//             'ubicacion' => [
//                 'lat' => -34.7037,
//                 'lng' => -58.4816,
//                 'address' => 'Nueva ubicación',
//                 'radius' => 1000
//             ]
//         ];

//         $response = $this->putJson("/api/alarms/{$alarma->id}", $updateData);

//         $response->assertStatus(200);

//         $alarma->refresh();
//         $this->assertEquals(-34.7037, $alarma->ubicacion['lat']);
//         $this->assertEquals(-58.4816, $alarma->ubicacion['lng']);
//     }

//     /** @test */
//     public function it_can_trigger_alarm_notification_when_entering_location()
//     {
//         $elemento = Elemento::factory()->create([
//             'tipo' => 'alarma',
//             'cuenta_id' => $this->cuenta->id
//         ]);
//         $alarma = Alarma::factory()->create([
//             'elemento_id' => $elemento->id,
//             'tipo_alarma' => 'geolocalizada',
//             'ubicacion' => [
//                 'lat' => -34.6037,
//                 'lng' => -58.3816,
//                 'radius' => 500
//             ]
//         ]);

//         // Simular que el usuario está entrando en el radio de la alarma
//         $response = $this->postJson('/api/alarms/check-location', [
//             'lat' => -34.6037,
//             'lng' => -58.3816
//         ]);

//         $response->assertStatus(200);

//         // Verificar que retorna alarmas activadas
//         $data = $response->json();
//         $this->assertArrayHasKey('triggered_alarms', $data);
//     }
}
