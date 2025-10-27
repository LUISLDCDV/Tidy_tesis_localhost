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
        Schema::table('tipos_notas', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('puntos_necesarios');
        });

        // Marcar los últimos 3 tipos como premium
        DB::table('tipos_notas')
            ->whereIn('id', [14, 15, 16])
            ->update(['is_premium' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_notas', function (Blueprint $table) {
            $table->dropColumn('is_premium');
        });
    }
};
