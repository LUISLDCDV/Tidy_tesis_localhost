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
        // Actualizar el enum para incluir el nuevo tipo de logro email_verified
        DB::statement("ALTER TABLE achievements MODIFY COLUMN type ENUM(
            'element_created',
            'goal_completed',
            'level_reached',
            'daily_streak',
            'special',
            'goal_with_many_metas',
            'metas_count',
            'email_verified'
        )");

        // También actualizar condition_type para incluir verified
        DB::statement("ALTER TABLE achievements MODIFY COLUMN condition_type ENUM(
            'count',
            'level',
            'days',
            'time_based',
            'metas_count',
            'verified'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volver al enum anterior
        DB::statement("ALTER TABLE achievements MODIFY COLUMN type ENUM(
            'element_created',
            'goal_completed',
            'level_reached',
            'daily_streak',
            'special',
            'goal_with_many_metas',
            'metas_count'
        )");

        DB::statement("ALTER TABLE achievements MODIFY COLUMN condition_type ENUM(
            'count',
            'level',
            'days',
            'time_based',
            'metas_count'
        )");
    }
};
