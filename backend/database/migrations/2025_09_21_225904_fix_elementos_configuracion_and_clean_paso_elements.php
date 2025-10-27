<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna configuracion si no existe
        if (!Schema::hasColumn('elementos', 'configuracion')) {
            Schema::table('elementos', function (Blueprint $table) {
                $table->json('configuracion')->nullable()->after('orden');
            });
        }

        // 2. Eliminar todos los elementos tipo 'paso' que causan problemas
        DB::table('elementos')->where('tipo', 'paso')->delete();

        // 3. Log de la operación
        \Log::info("✅ Migración fix_elementos_configuracion_and_clean_paso_elements ejecutada");
        \Log::info("   - Columna configuracion agregada/verificada");
        \Log::info("   - Elementos tipo 'paso' eliminados");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En down no eliminamos la columna configuracion por seguridad
        // Solo loggeamos que se ejecutó el rollback
        \Log::info("⚠️ Rollback de fix_elementos_configuracion_and_clean_paso_elements");
    }
};
