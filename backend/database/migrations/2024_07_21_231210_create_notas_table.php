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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('elemento_id');
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');
            
            $table->foreignId('tipo_nota_id')->constrained('tipos_notas')->onDelete('cascade');


            $table->date('fecha');
            $table->string('nombre', 100);
            $table->text('informacion')->nullable();
            $table->json('contenido'); // Tipo JSON
            $table->string('clave', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
