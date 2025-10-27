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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
};