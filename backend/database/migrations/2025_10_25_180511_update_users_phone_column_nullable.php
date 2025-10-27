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
        // Primero, actualizar todos los valores vacíos a NULL
        DB::statement('UPDATE users SET phone = NULL WHERE phone = ""');

        // Luego eliminar el índice unique si existe
        try {
            DB::statement('ALTER TABLE users DROP INDEX users_phone_unique');
        } catch (\Exception $e) {
            // El índice puede no existir, ignorar
        }

        // Finalmente hacer la columna nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable(false)->unique()->change();
        });
    }
};
