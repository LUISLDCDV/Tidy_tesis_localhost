<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventosTable extends Migration
{
    public function up()
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('calendario_id');
            $table->foreign('calendario_id')->references('id')->on('calendarios')->onDelete('cascade');
            
            $table->unsignedBigInteger('elemento_id');
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');
            
            $table->string('tipo', 50);
            $table->date('fechaVencimiento');
            $table->time('horaVencimiento');
            $table->string('nombre', 100);
            $table->text('informacion')->nullable();
            $table->json('gps')->nullable();
            $table->json('clima')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventos');
    }
}
