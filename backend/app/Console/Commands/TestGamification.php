<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;
use App\Services\GamificationService;
use Illuminate\Support\Facades\Log;

class TestGamification extends Command
{
    protected $signature = 'test:gamification {user_id=1}';
    protected $description = 'Test gamification system by creating a test element';

    public function handle()
    {
        $userId = $this->argument('user_id');

        $this->info("Testing gamification system for user ID: {$userId}");

        // First, let's find or create a cuenta for this user
        $cuenta = UsuarioCuenta::where('user_id', $userId)->first();

        if (!$cuenta) {
            $this->error("No se encontró cuenta para user_id: {$userId}");
            return 1;
        }

        $this->info("Found cuenta ID: {$cuenta->id} for user_id: {$userId}");

        // Create a test element
        $elemento = new Elemento();
        $elemento->tipo = 'nota';
        $elemento->descripcion = 'Test element for gamification system';
        $elemento->estado = 'pendiente';
        $elemento->cuenta_id = $cuenta->id;
        $elemento->save();

        $this->info("Created test element with ID: {$elemento->id}");

        // Check if observer was triggered
        $this->info("Check the Laravel logs to see if ElementoGamificationObserver was triggered");

        // Also test the gamification service directly
        $gamificationService = app(GamificationService::class);
        $this->info("Testing GamificationService directly...");

        try {
            $result = $gamificationService->processElementCreated($userId, 'nota');
            $this->info("GamificationService result: " . json_encode($result));
        } catch (\Exception $e) {
            $this->error("GamificationService error: " . $e->getMessage());
        }

        return 0;
    }
}