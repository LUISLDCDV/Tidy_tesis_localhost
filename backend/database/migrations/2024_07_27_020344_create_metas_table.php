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
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('elemento_id');
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');
            
            $table->unsignedBigInteger('objetivo_id');
            $table->foreign('objetivo_id')->references('id')->on('objetivos')->onDelete('cascade');
            
            $table->string('tipo', 50);
            $table->date('fechaCreacion');
            $table->date('fechaVencimiento')->nullable();
            $table->string('nombre', 100);
            $table->text('informacion')->nullable();
            $table->string('status', 50); // Campo status

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
