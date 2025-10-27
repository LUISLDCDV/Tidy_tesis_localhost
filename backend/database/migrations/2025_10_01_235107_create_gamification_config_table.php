<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gamification_config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ej: 'xp_crear_alarma', 'xp_completar_objetivo'
            $table->integer('value'); // Puntos de XP
            $table->string('description')->nullable(); // Descripción de qué es
            $table->timestamps();
        });

        // Insertar valores por defecto
        DB::table('gamification_config')->insert([
            // XP por crear elementos
            ['key' => 'xp_crear_alarma', 'value' => 5, 'description' => 'XP por crear una alarma', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'xp_crear_objetivo', 'value' => 10, 'description' => 'XP por crear un objetivo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'xp_crear_meta', 'value' => 8, 'description' => 'XP por crear una meta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'xp_crear_calendario', 'value' => 5, 'description' => 'XP por crear un calendario', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'xp_crear_evento', 'value' => 5, 'description' => 'XP por crear un evento', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'xp_crear_nota', 'value' => 3, 'description' => 'XP por crear una nota', 'created_at' => now(), 'updated_at' => now()],

            // XP por completar elementos
            ['key' => 'xp_completar_objetivo', 'value' => 15, 'description' => 'XP por completar un objetivo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'xp_completar_meta', 'value' => 12, 'description' => 'XP por completar una meta', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_config');
    }
};
