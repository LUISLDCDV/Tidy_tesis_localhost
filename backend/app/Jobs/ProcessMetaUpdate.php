<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Elementos\Meta;
use App\Models\Elementos\Elemento;
use App\Services\ExperienciaService;
use Illuminate\Support\Facades\Log;

class ProcessMetaUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $metaId;
    protected $updateData;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($metaId, $updateData, $userId)
    {
        $this->metaId = $metaId;
        $this->updateData = $updateData;
        $this->userId = $userId;

        // Configurar la cola y delay
        $this->onQueue('meta_updates');
        $this->delay(now()->addSeconds(1)); // Delay de 1 segundo para evitar spam
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("🔄 ProcessMetaUpdate - Iniciando actualización", [
                'meta_id' => $this->metaId,
                'user_id' => $this->userId,
                'data' => $this->updateData
            ]);

            $meta = Meta::find($this->metaId);

            if (!$meta) {
                Log::warning("⚠️ Meta no encontrada", ['meta_id' => $this->metaId]);
                return;
            }

            // Actualizar la meta
            $meta->update($this->updateData);

            Log::info("✅ Meta actualizada exitosamente", [
                'meta_id' => $this->metaId,
                'status' => $meta->status
            ]);

            // Si la meta fue completada, procesar experiencia
            if (isset($this->updateData['status']) && $this->updateData['status'] === 'completada') {
                $this->processExperienceUpdate($meta);
            }

        } catch (\Exception $e) {
            Log::error("❌ Error procesando actualización de meta", [
                'meta_id' => $this->metaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Procesar actualización de experiencia cuando se completa una meta
     */
    private function processExperienceUpdate(Meta $meta)
    {
        try {
            // Encontrar el objetivo padre
            $objetivo = Elemento::find($meta->elemento_id);

            if (!$objetivo) {
                Log::warning("⚠️ Objetivo no encontrado para meta", ['meta_id' => $meta->id]);
                return;
            }

            Log::info("🎯 Procesando experiencia para meta completada", [
                'meta_id' => $meta->id,
                'objetivo_id' => $objetivo->id
            ]);

            // Usar el servicio de experiencia para actualizar
            $experienciaService = new ExperienciaService();
            $experienciaService->procesarCompletarMeta($this->userId, $meta, $objetivo);

        } catch (\Exception $e) {
            Log::error("❌ Error procesando experiencia", [
                'meta_id' => $meta->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [2, 5, 10];

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("❌ ProcessMetaUpdate falló después de todos los reintentos", [
            'meta_id' => $this->metaId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}