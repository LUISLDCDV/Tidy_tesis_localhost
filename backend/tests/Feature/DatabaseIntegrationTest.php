<?php

namespace Tests\Feature;

use App\Models\Elementos\Alarma;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Nota;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_properly_handles_database_transactions()
    {
        $initialUserCount = User::count();

        DB::transaction(function () {
            User::factory()->create(['email' => 'test1@example.com']);
            User::factory()->create(['email' => 'test2@example.com']);
        });

        $this->assertEquals($initialUserCount + 2, User::count());
    }

    /** @test */
    public function it_handles_database_rollback_on_failure()
    {
        $initialUserCount = User::count();

        try {
            DB::transaction(function () {
                User::factory()->create(['email' => 'test1@example.com']);
                // This should fail due to duplicate email
                User::factory()->create(['email' => 'test1@example.com']);
            });
        } catch (\Exception $e) {
            // Expected to fail
        }

        $this->assertEquals($initialUserCount, User::count());
    }

    /** @test */
    public function it_maintains_referential_integrity()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        $elemento = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $cuenta->id
        ]);
        $alarma = Alarma::factory()->create(['elemento_id' => $elemento->id]);

        // Test cascade relationships
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('usuario_cuentas', ['user_id' => $user->id]);
        $this->assertDatabaseHas('elementos', ['cuenta_id' => $cuenta->id]);
        $this->assertDatabaseHas('alarmas', ['elemento_id' => $elemento->id]);

        // Delete user should handle cascades properly
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_handles_concurrent_access_properly()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        // Simulate concurrent creation of elementos
        $elementos = [];
        for ($i = 0; $i < 5; $i++) {
            $elementos[] = Elemento::create([
                'tipo' => 'nota',
                'descripcion' => "Elemento concurrent {$i}",
                'estado' => 'activo',
                'orden' => $i + 1,
                'cuenta_id' => $cuenta->id
            ]);
        }

        $this->assertCount(5, $elementos);
        $this->assertEquals(5, Elemento::where('cuenta_id', $cuenta->id)->count());
    }

    /** @test */
    public function it_handles_large_json_data_properly()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $cuenta->id
        ]);

        // Create large content
        $largeContent = [
            'sections' => [],
            'metadata' => [
                'created' => now()->toISOString(),
                'wordCount' => 0
            ]
        ];

        // Generate 100 sections with content
        for ($i = 0; $i < 100; $i++) {
            $largeContent['sections'][] = [
                'id' => $i,
                'title' => "Section {$i}",
                'content' => str_repeat("This is content for section {$i}. ", 50),
                'tags' => ['section', "tag{$i}", 'auto-generated'],
                'created' => now()->addMinutes($i)->toISOString()
            ];
        }

        $largeContent['metadata']['wordCount'] = count($largeContent['sections']) * 50;

        $nota = Nota::create([
            'elemento_id' => $elemento->id,
            'nombre' => 'Large Content Note',
            'informacion' => 'Note with extensive content',
            'contenido' => $largeContent,
            'fecha' => now()->toDateString()
        ]);

        $this->assertInstanceOf(Nota::class, $nota);
        $this->assertCount(100, $nota->contenido['sections']);
        $this->assertEquals(5000, $nota->contenido['metadata']['wordCount']);

        // Test retrieval
        $retrieved = Nota::find($nota->id);
        $this->assertEquals($largeContent, $retrieved->contenido);
    }

    /** @test */
    public function it_handles_database_queries_efficiently()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        // Create multiple elementos with related data
        for ($i = 0; $i < 10; $i++) {
            $elemento = Elemento::factory()->create([
                'tipo' => $i % 2 === 0 ? 'nota' : 'alarma',
                'cuenta_id' => $cuenta->id
            ]);

            if ($elemento->tipo === 'nota') {
                Nota::factory()->create(['elemento_id' => $elemento->id]);
            } else {
                Alarma::factory()->create(['elemento_id' => $elemento->id]);
            }
        }

        // Test efficient querying with eager loading
        DB::enableQueryLog();

        $elementos = Elemento::with(['nota', 'alarma'])
            ->where('cuenta_id', $cuenta->id)
            ->get();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(10, $elementos);
        // Should use efficient queries (not N+1)
        $this->assertLessThan(5, count($queryLog));
    }

    /** @test */
    public function it_handles_soft_deletes_correctly()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        $elemento = Elemento::factory()->create([
            'tipo' => 'nota',
            'cuenta_id' => $cuenta->id
        ]);
        $nota = Nota::factory()->create(['elemento_id' => $elemento->id]);

        // Soft delete the elemento
        $elemento->delete();

        $this->assertSoftDeleted('elementos', ['id' => $elemento->id]);
        $this->assertDatabaseHas('notas', ['id' => $nota->id]);

        // Test that queries exclude soft deleted by default
        $this->assertEquals(0, Elemento::where('cuenta_id', $cuenta->id)->count());

        // Test that we can include soft deleted
        $this->assertEquals(1, Elemento::withTrashed()->where('cuenta_id', $cuenta->id)->count());

        // Test restore
        $elemento->restore();
        $this->assertEquals(1, Elemento::where('cuenta_id', $cuenta->id)->count());
    }

    /** @test */
    public function it_handles_database_constraints_properly()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        // Test unique constraint on user email
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $user->email]);
    }

    /** @test */
    public function it_handles_json_queries_correctly()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);
        $elemento1 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $cuenta->id
        ]);
        $elemento2 = Elemento::factory()->create([
            'tipo' => 'alarma',
            'cuenta_id' => $cuenta->id
        ]);

        // Create alarms with GPS configuration
        Alarma::factory()->create([
            'elemento_id' => $elemento1->id,
            'configuraciones' => [
                'gps' => ['latitude' => -34.6037, 'longitude' => -58.3816, 'radius' => 100],
                'clima' => ['enabled' => true]
            ]
        ]);

        Alarma::factory()->create([
            'elemento_id' => $elemento2->id,
            'configuraciones' => [
                'gps' => ['latitude' => -34.7037, 'longitude' => -58.4816, 'radius' => 200],
                'clima' => ['enabled' => false]
            ]
        ]);

        // Test JSON queries
        $gpsEnabledAlarms = Alarma::whereJsonContains('configuraciones->clima->enabled', true)->get();
        $this->assertCount(1, $gpsEnabledAlarms);

        $alarmsWithSmallRadius = Alarma::whereJsonContains('configuraciones->gps->radius', 100)->get();
        $this->assertCount(1, $alarmsWithSmallRadius);
    }

    /** @test */
    public function it_handles_pagination_efficiently()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        // Create 50 elementos
        for ($i = 0; $i < 50; $i++) {
            $elemento = Elemento::factory()->create([
                'tipo' => 'nota',
                'cuenta_id' => $cuenta->id,
                'descripcion' => "Elemento {$i}"
            ]);
            Nota::factory()->create(['elemento_id' => $elemento->id]);
        }

        // Test pagination
        $page1 = Elemento::where('cuenta_id', $cuenta->id)
            ->paginate(10, ['*'], 'page', 1);

        $page2 = Elemento::where('cuenta_id', $cuenta->id)
            ->paginate(10, ['*'], 'page', 2);

        $this->assertCount(10, $page1->items());
        $this->assertCount(10, $page2->items());
        $this->assertEquals(50, $page1->total());
        $this->assertEquals(5, $page1->lastPage());
    }

    /** @test */
    public function it_handles_database_indexing_efficiently()
    {
        $user = User::factory()->create();
        $cuenta = UsuarioCuenta::factory()->create(['user_id' => $user->id]);

        // Create many elementos to test indexing
        for ($i = 0; $i < 100; $i++) {
            Elemento::factory()->create([
                'tipo' => $i % 3 === 0 ? 'nota' : ($i % 3 === 1 ? 'alarma' : 'objetivo'),
                'estado' => $i % 2 === 0 ? 'activo' : 'inactivo',
                'cuenta_id' => $cuenta->id,
                'created_at' => now()->subDays($i % 30)
            ]);
        }

        DB::enableQueryLog();

        // Test indexed queries
        $activosCount = Elemento::where('cuenta_id', $cuenta->id)
            ->where('estado', 'activo')
            ->count();

        $notasCount = Elemento::where('cuenta_id', $cuenta->id)
            ->where('tipo', 'nota')
            ->count();

        $recentCount = Elemento::where('cuenta_id', $cuenta->id)
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertGreaterThan(0, $activosCount);
        $this->assertGreaterThan(0, $notasCount);
        $this->assertGreaterThan(0, $recentCount);

        // Queries should be efficient
        foreach ($queryLog as $query) {
            $this->assertLessThan(100, $query['time']); // Less than 100ms
        }
    }
}