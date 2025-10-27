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
        Schema::create('alarmas', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('elemento_id');
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');
            
            $table->string('nombre', 100);
            $table->date('fecha')->nullable(); // Fecha de activación
            $table->time('hora');
            $table->date('fechaVencimiento')->nullable(); // Fecha de vencimiento
            $table->time('horaVencimiento')->nullable(); // Hora de vencimiento
            $table->text('informacion')->nullable(); // Información adicional
            $table->integer('intensidad_volumen')->nullable();
            $table->json('gps')->nullable();
            $table->json('distancia_radio')->nullable();
            $table->json('configuraciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarmas');
    }
};
