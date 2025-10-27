<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Achievement;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear el nuevo logro
        Achievement::create([
            'name' => 'Planificador Maestro',
            'description' => 'Completa un objetivo con más de 5 metas',
            'icon' => '🎯',
            'type' => 'goal_with_many_metas',
            'condition_type' => 'metas_count',
            'condition_value' => 5,
            'experience_reward' => 500,
            'is_active' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el logro
        Achievement::where('type', 'goal_with_many_metas')->delete();
    }
};
