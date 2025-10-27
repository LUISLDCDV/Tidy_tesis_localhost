<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_get_authenticated_user_profile()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user-data');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email', 'created_at'],
                    'cuenta' => ['id', 'user_id']
                ])
                ->assertJson([
                    'user' => [
                        'id' => $user->id,
                        'name' => 'John Doe',
                        'email' => 'john@example.com'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_user_profile()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson('/api/profile', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function it_validates_profile_update_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Test invalid email
        $response = $this->putJson('/api/profile', [
            'name' => 'Valid Name',
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test empty name
        $response = $this->putJson('/api/profile', [
            'name' => '',
            'email' => 'valid@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_prevents_duplicate_email_on_profile_update()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        Sanctum::actingAs($user1);

        $response = $this->putJson('/api/profile', [
            'name' => 'User 1',
            'email' => 'user2@example.com' // Already taken by user2
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_allows_keeping_same_email_on_profile_update()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'user@example.com'
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'user@example.com' // Same email
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'user@example.com'
        ]);
    }

    /** @test */
    public function it_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password')
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'old_password',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123'
        ]);

        $response->assertStatus(200);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('new_password123', $user->password));
    }

    /** @test */
    public function it_validates_password_change_data()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password')
        ]);

        Sanctum::actingAs($user);

        // Test wrong current password
        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'wrong_password',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_password']);

        // Test password confirmation mismatch
        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'old_password',
            'password' => 'new_password123',
            'password_confirmation' => 'different_password'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Test short password
        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'old_password',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_can_delete_user_account()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/profile', [
            'password' => 'password123'
        ]);

        $response->assertStatus(200);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_validates_password_for_account_deletion()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/profile', [
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_can_upload_profile_avatar()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/avatar', [
            'avatar' => 'data:image/jpeg;base64,' . base64_encode('fake_image_data')
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['avatar_url']);
    }

    /** @test */
    public function it_validates_avatar_upload()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Test invalid base64
        $response = $this->postJson('/api/profile/avatar', [
            'avatar' => 'invalid_data'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    public function it_can_get_user_statistics()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'total_elementos',
                    'notas_count',
                    'alarmas_count',
                    'objetivos_count',
                    'elementos_activos',
                    'elementos_completados'
                ]);
    }

    /** @test */
    public function it_can_get_user_activity_log()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user/activity');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'action',
                            'description',
                            'created_at'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_user_preferences()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $preferences = [
            'theme' => 'dark',
            'language' => 'es',
            'notifications' => [
                'email' => true,
                'push' => false,
                'sms' => true
            ],
            'privacy' => [
                'profile_visibility' => 'private',
                'data_sharing' => false
            ]
        ];

        $response = $this->putJson('/api/user/preferences', [
            'preferences' => $preferences
        ]);

        $response->assertStatus(200);

        // Verify preferences were saved to user account
        $cuenta->refresh();
        $this->assertEquals($preferences['theme'], $cuenta->configuracion['theme'] ?? null);
    }

    /** @test */
    public function it_can_export_user_data()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user/export');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user_data',
                    'elementos_data',
                    'export_date',
                    'total_records'
                ]);
    }

    /** @test */
    public function it_requires_authentication_for_user_endpoints()
    {
        $endpoints = [
            'GET /api/user-data',
            'PUT /api/profile',
            'PUT /api/profile/password',
            'DELETE /api/profile',
            'GET /api/user/stats',
            'GET /api/user/activity',
            'PUT /api/user/preferences',
            'GET /api/user/export'
        ];

        foreach ($endpoints as $endpoint) {
            [$method, $url] = explode(' ', $endpoint);

            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function it_handles_user_session_management()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Get active sessions
        $response = $this->getJson('/api/user/sessions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'device',
                            'ip_address',
                            'last_activity',
                            'is_current'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_revoke_user_sessions()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        // Authenticate with the token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson('/api/user/sessions/all');

        $response->assertStatus(200);

        // Token should be revoked
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/user-data');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_enable_two_factor_authentication()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/user/2fa/enable');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'qr_code',
                    'secret_key',
                    'backup_codes'
                ]);
    }

    /** @test */
    public function it_can_verify_two_factor_authentication()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First enable 2FA
        $enableResponse = $this->postJson('/api/user/2fa/enable');
        $enableResponse->assertStatus(200);

        // Then verify with a code (mocked)
        $response = $this->postJson('/api/user/2fa/verify', [
            'code' => '123456'
        ]);

        // This would normally verify against a real TOTP code
        // For testing, we can mock the verification
        $response->assertStatus(200);
    }
}