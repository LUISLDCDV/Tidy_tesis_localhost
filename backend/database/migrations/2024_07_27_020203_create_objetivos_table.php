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
        Schema::create('objetivos', function (Blueprint $table) {
            $table->id('id');
            $table->string('tipo', 50);
            
            $table->unsignedBigInteger('elemento_id');
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');
            
            $table->date('fechaCreacion');
            $table->date('fechaVencimiento')->nullable();
            $table->string('nombre', 100);
            $table->text('informacion')->nullable();
            $table->integer('numero_de_metas')->default(0);
            $table->integer('metas_logradas')->default(0);
            $table->integer('días_de_racha')->default(0); 
            $table->string('status', 50); 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivos');
    }
};
