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
        Schema::table('alarmas', function (Blueprint $table) {
            $table->string('tipo_alarma', 50)->nullable()->after('intensidad_volumen');
            $table->json('ubicacion')->nullable()->after('tipo_alarma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alarmas', function (Blueprint $table) {
            $table->dropColumn(['tipo_alarma', 'ubicacion']);
        });
    }
};
