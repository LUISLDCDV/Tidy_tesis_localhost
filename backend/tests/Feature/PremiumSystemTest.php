<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PremiumSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $cuenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cuenta = UsuarioCuenta::factory()->create([
            'user_id' => $this->user->id
        ]);
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_check_premium_status()
    {
        $this->cuenta->update([
            'is_premium' => true,
            'premium_expires_at' => now()->addMonths(1)
        ]);

        $response = $this->getJson('/api/user-data');

        $response->assertStatus(200)
                ->assertJson([
                    'cuenta' => [
                        'is_premium' => true
                    ]
                ]);
    }

    /** @test */
    public function it_detects_expired_premium_subscription()
    {
        $this->cuenta->update([
            'is_premium' => true,
            'premium_expires_at' => now()->subDays(1)
        ]);

        $response = $this->getJson('/api/premium/status');

        $response->assertStatus(200);
        $data = $response->json();

        // El sistema debería detectar que la suscripción expiró
        $this->assertTrue(
            isset($data['is_expired']) && $data['is_expired'] === true ||
            isset($data['is_premium']) && $data['is_premium'] === false
        );
    }

    /** @test */
    public function non_premium_users_cannot_create_premium_elements()
    {
        $this->cuenta->update(['is_premium' => false]);

        // Intentar crear una nota premium sin ser premium
        $response = $this->postJson('/api/notes', [
            'nombre' => 'Premium Note',
            'informacion' => 'This is premium',
            'tipo_nota_id' => 14 // Tipo premium (Planificación de Viajes)
        ]);

        // Podría retornar 403 Forbidden o 422 con error de validación
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 422
        );
    }

    /** @test */
    public function premium_users_can_create_premium_elements()
    {
        $this->cuenta->update([
            'is_premium' => true,
            'premium_expires_at' => now()->addMonths(1),
            'nivel' => 1 // Premium notes ahora están disponibles en nivel 1
        ]);

        $response = $this->postJson('/api/notes', [
            'nombre' => 'My Premium Note',
            'informacion' => 'Premium content',
            'tipo_nota_id' => 14 // Tipo premium
        ]);

        $this->assertTrue(
            $response->status() === 201 ||
            $response->status() === 200
        );
    }

    /** @test */
    public function it_can_retrieve_premium_payment_history()
    {
        // Crear un registro de pago
        \App\Models\PremiumPayment::create([
            'cuenta_id' => $this->cuenta->id,
            'amount' => 999.99,
            'currency' => 'ARS',
            'status' => 'approved',
            'payment_method' => 'mercadopago',
            'external_reference' => 'MP-TEST-123'
        ]);

        $response = $this->getJson('/api/premium/payments');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'currency',
                            'status',
                            'created_at'
                        ]
                    ]
                ]);
    }
}
