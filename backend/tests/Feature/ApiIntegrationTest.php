<?php

namespace Tests\Feature;

use App\Models\Elementos\Alarma;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Nota;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
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
    public function it_handles_complete_elemento_workflow()
    {
        // 1. Create a note
        $noteData = [
            'nombre' => 'Meeting Notes',
            'informacion' => 'Important meeting discussion',
            'contenido' => [
                'text' => 'Key points from the meeting',
                'tags' => ['work', 'important']
            ]
        ];

        $noteResponse = $this->postJson('/api/notes', $noteData);
        $noteResponse->assertStatus(201);
        $noteId = $noteResponse->json('data.id');

        // 2. Update the note
        $updateData = [
            'nombre' => 'Updated Meeting Notes',
            'contenido' => [
                'text' => 'Updated key points from the meeting',
                'tags' => ['work', 'important', 'updated'],
                'action_items' => ['Follow up with team', 'Schedule next meeting']
            ]
        ];

        $updateResponse = $this->putJson("/api/notes/{$noteId}", $updateData);
        $updateResponse->assertStatus(200);

        // 3. Create an alarm related to the meeting
        $alarmData = [
            'nombre' => 'Meeting Follow-up',
            'informacion' => 'Reminder to follow up on meeting',
            'fecha' => now()->addDays(3)->toDateString(),
            'hora' => '09:00:00'
        ];

        $alarmResponse = $this->postJson('/api/alarms', $alarmData);
        $alarmResponse->assertStatus(201);
        $alarmId = $alarmResponse->json('data.id');

        // 4. Get all elementos for user
        $elementosResponse = $this->getJson('/api/elementos');
        $elementosResponse->assertStatus(200)
                         ->assertJsonCount(2, 'data');

        // 5. Search elementos
        $searchResponse = $this->getJson('/api/elementos?search=meeting');
        $searchResponse->assertStatus(200)
                      ->assertJsonCount(2, 'data');

        // 6. Delete the alarm
        $deleteResponse = $this->deleteJson("/api/alarms/{$alarmId}");
        $deleteResponse->assertStatus(200);

        // 7. Verify only note remains
        $finalResponse = $this->getJson('/api/elementos');
        $finalResponse->assertStatus(200)
                     ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_handles_cors_preflight_requests()
    {
        $response = $this->options('/api/notes', [], [
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization'
        ]);

        $response->assertStatus(200)
                ->assertHeader('Access-Control-Allow-Origin', '*')
                ->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }

    /** @test */
    public function it_handles_api_rate_limiting()
    {
        // Make many requests to test rate limiting
        $responses = [];

        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/user-data');
        }

        // All requests should succeed initially
        foreach (array_slice($responses, 0, 5) as $response) {
            $response->assertStatus(200);
        }

        // If rate limiting is implemented, later requests might be limited
        // This is a basic test - real rate limiting would require more specific testing
    }

    /** @test */
    public function it_handles_api_pagination_correctly()
    {
        // Create many notes
        for ($i = 0; $i < 25; $i++) {
            $elemento = Elemento::factory()->create([
                'tipo' => 'nota',
                'cuenta_id' => $this->cuenta->id
            ]);
            Nota::factory()->create(['elemento_id' => $elemento->id]);
        }

        // Test pagination
        $page1 = $this->getJson('/api/notes?page=1&per_page=10');
        $page1->assertStatus(200)
              ->assertJsonCount(10, 'data')
              ->assertJsonPath('meta.current_page', 1)
              ->assertJsonPath('meta.per_page', 10)
              ->assertJsonPath('meta.total', 25);

        $page2 = $this->getJson('/api/notes?page=2&per_page=10');
        $page2->assertStatus(200)
              ->assertJsonCount(10, 'data')
              ->assertJsonPath('meta.current_page', 2);

        $page3 = $this->getJson('/api/notes?page=3&per_page=10');
        $page3->assertStatus(200)
              ->assertJsonCount(5, 'data')
              ->assertJsonPath('meta.current_page', 3);
    }

    /** @test */
    public function it_handles_api_filtering_and_sorting()
    {
        // Create notes with different dates and names
        $dates = ['2024-01-15', '2024-02-15', '2024-03-15'];
        $names = ['Alpha Note', 'Beta Note', 'Gamma Note'];

        for ($i = 0; $i < 3; $i++) {
            $elemento = Elemento::factory()->create([
                'tipo' => 'nota',
                'cuenta_id' => $this->cuenta->id
            ]);
            Nota::factory()->create([
                'elemento_id' => $elemento->id,
                'nombre' => $names[$i],
                'fecha' => $dates[$i]
            ]);
        }

        // Test date filtering
        $filtered = $this->getJson('/api/notes?fecha_inicio=2024-02-01&fecha_fin=2024-02-28');
        $filtered->assertStatus(200)
                ->assertJsonCount(1, 'data');

        // Test name sorting
        $sorted = $this->getJson('/api/notes?sort_by=nombre&sort_order=asc');
        $sorted->assertStatus(200);
        $data = $sorted->json('data');
        $this->assertEquals('Alpha Note', $data[0]['nombre']);

        // Test search
        $searched = $this->getJson('/api/notes?search=beta');
        $searched->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_handles_api_error_responses_consistently()
    {
        // Test 404 for non-existent resource
        $notFound = $this->getJson('/api/notes/99999');
        $notFound->assertStatus(404)
                ->assertJsonStructure(['error', 'message']);

        // Test 422 for validation errors
        $invalid = $this->postJson('/api/notes', [
            'nombre' => '', // Required field empty
            'informacion' => 'Test'
        ]);
        $invalid->assertStatus(422)
               ->assertJsonStructure(['message', 'errors']);

        // Test unauthorized access
        Sanctum::actingAs(null);
        $unauthorized = $this->getJson('/api/notes');
        $unauthorized->assertStatus(401)
                    ->assertJsonStructure(['message']);
    }

    /** @test */
    public function it_handles_bulk_operations()
    {
        // Create multiple elementos
        $elementos = [];
        for ($i = 0; $i < 5; $i++) {
            $elemento = Elemento::factory()->create([
                'tipo' => 'nota',
                'cuenta_id' => $this->cuenta->id
            ]);
            $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);
            $elementos[] = $nota->id;
        }

        // Test bulk delete
        $bulkDelete = $this->deleteJson('/api/notes/bulk', [
            'ids' => array_slice($elementos, 0, 3)
        ]);
        $bulkDelete->assertStatus(200);

        // Verify only 2 remain
        $remaining = $this->getJson('/api/notes');
        $remaining->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        // Test bulk update
        $bulkUpdate = $this->putJson('/api/notes/bulk', [
            'ids' => array_slice($elementos, 3),
            'data' => ['informacion' => 'Bulk updated']
        ]);
        $bulkUpdate->assertStatus(200);
    }

    /** @test */
    public function it_handles_complex_search_queries()
    {
        // Create notes with various content
        $testData = [
            ['nombre' => 'Work Meeting', 'contenido' => ['text' => 'Quarterly review meeting', 'tags' => ['work', 'meeting']]],
            ['nombre' => 'Personal Goal', 'contenido' => ['text' => 'Exercise 3 times per week', 'tags' => ['health', 'personal']]],
            ['nombre' => 'Project Ideas', 'contenido' => ['text' => 'New app development ideas', 'tags' => ['work', 'development']]]
        ];

        foreach ($testData as $data) {
            $elemento = Elemento::factory()->create([
                'tipo' => 'nota',
                'cuenta_id' => $this->cuenta->id
            ]);
            Nota::factory()->create([
                'elemento_id' => $elemento->id,
                'nombre' => $data['nombre'],
                'contenido' => $data['contenido']
            ]);
        }

        // Search by tags
        $workNotes = $this->getJson('/api/notes?tags=work');
        $workNotes->assertStatus(200)
                 ->assertJsonCount(2, 'data');

        // Search by text content
        $meetingNotes = $this->getJson('/api/notes?search=meeting');
        $meetingNotes->assertStatus(200)
                    ->assertJsonCount(2, 'data'); // 'meeting' appears in name and content

        // Combined search
        $combined = $this->getJson('/api/notes?search=app&tags=development');
        $combined->assertStatus(200)
               ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_handles_api_versioning()
    {
        // Test API version header
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'API-Version' => 'v1'
        ])->getJson('/api/notes');

        $response->assertStatus(200)
                ->assertHeader('API-Version', 'v1');
    }

    /** @test */
    public function it_handles_concurrent_api_requests()
    {
        // Simulate concurrent note creation
        $promises = [];
        $noteData = [
            'nombre' => 'Concurrent Note',
            'informacion' => 'Test concurrent creation'
        ];

        // In a real scenario, this would test actual concurrency
        // For now, we'll test sequential creation to ensure consistency
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/notes', array_merge($noteData, [
                'nombre' => "Concurrent Note {$i}"
            ]));
            $response->assertStatus(201);
        }

        $allNotes = $this->getJson('/api/notes');
        $allNotes->assertStatus(200)
                ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_maintains_data_consistency_across_api_calls()
    {
        // Create a note
        $noteResponse = $this->postJson('/api/notes', [
            'nombre' => 'Consistency Test',
            'informacion' => 'Testing data consistency'
        ]);
        $noteId = $noteResponse->json('data.id');

        // Get the note
        $getResponse = $this->getJson("/api/notes/{$noteId}");
        $originalData = $getResponse->json('data');

        // Update the note
        $this->putJson("/api/notes/{$noteId}", [
            'nombre' => 'Updated Consistency Test'
        ]);

        // Get the note again
        $updatedResponse = $this->getJson("/api/notes/{$noteId}");
        $updatedData = $updatedResponse->json('data');

        // Verify consistency
        $this->assertEquals($originalData['id'], $updatedData['id']);
        $this->assertEquals('Updated Consistency Test', $updatedData['nombre']);
        $this->assertEquals($originalData['informacion'], $updatedData['informacion']);
    }
}