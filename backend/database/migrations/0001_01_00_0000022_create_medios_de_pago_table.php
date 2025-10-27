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
        Schema::create('medios_de_pago', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('tipo');
            $table->string('tipo_subscripcion');
            $table->json('datos_ocultos');
            $table->string('identificador')->nullable(); // Puede ser null si no se aplica
            $table->date('fecha_expiracion')->nullable(); // Opcional, para tarjetas de crédito, etc.
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medios_de_pago');
    }
};
