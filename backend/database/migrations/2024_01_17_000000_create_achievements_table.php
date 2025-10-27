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
                'special'
            ]);
            $table->enum('condition_type', [
                'count',
                'level',
                'days',
                'time_based'
            ]);
            $table->integer('condition_value');
            $table->integer('experience_reward')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('name');
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};