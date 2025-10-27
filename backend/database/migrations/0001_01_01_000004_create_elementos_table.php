<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementosTable extends Migration
{
    public function up()
    {
        Schema::create('elementos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cuenta_id');
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('cascade');


            $table->string('tipo', 50);
            $table->string('descripcion')->nullable();
            $table->string('estado', 20)->default('activo'); // Por ejemplo: activo, inactivo
            $table->string('imagen')->nullable(); // URL o identificador para imagen/icono
            $table->string('orden')->nullable(); // URL o identificador para imagen/icono
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('elementos');
    }
}
