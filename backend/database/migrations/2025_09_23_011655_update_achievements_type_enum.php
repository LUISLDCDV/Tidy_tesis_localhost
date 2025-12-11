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
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Actualizar el enum para incluir el nuevo tipo
            DB::statement("ALTER TABLE achievements MODIFY COLUMN type ENUM(
                'element_created',
                'goal_completed',
                'level_reached',
                'daily_streak',
                'special',
                'goal_with_many_metas',
                'metas_count'
            )");

            // También actualizar condition_type para incluir metas_count
            DB::statement("ALTER TABLE achievements MODIFY COLUMN condition_type ENUM(
                'count',
                'level',
                'days',
                'time_based',
                'metas_count'
            )");
        } elseif ($driver === 'sqlite') {
            // Para SQLite, necesitamos recrear la tabla con los nuevos valores
            // Guardar los datos existentes en una tabla temporal
            DB::statement('CREATE TEMPORARY TABLE achievements_temp AS SELECT * FROM achievements');

            // Eliminar la tabla original
            Schema::dropIfExists('achievements');

            // Crear la nueva tabla con los enum actualizados
            Schema::create('achievements', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->string('icon', 10)->default('🏆');
                $table->enum('type', [
                    'element_created',
                    'goal_completed',
                    'level_reached',
                    'daily_streak',
                    'special',
                    'goal_with_many_metas',
                    'metas_count'
                ]);
                $table->enum('condition_type', [
                    'count',
                    'level',
                    'days',
                    'time_based',
                    'metas_count'
                ]);
                $table->integer('condition_value');
                $table->integer('experience_reward')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('name');
                $table->index(['type', 'is_active']);
            });

            // Copiar los datos de vuelta
            DB::statement('INSERT INTO achievements SELECT * FROM achievements_temp');

            // Limpiar la tabla temporal
            DB::statement('DROP TABLE achievements_temp');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Volver al enum original
            DB::statement("ALTER TABLE achievements MODIFY COLUMN type ENUM(
                'element_created',
                'goal_completed',
                'level_reached',
                'daily_streak',
                'special'
            )");

            DB::statement("ALTER TABLE achievements MODIFY COLUMN condition_type ENUM(
                'count',
                'level',
                'days',
                'time_based'
            )");
        }
    }
};