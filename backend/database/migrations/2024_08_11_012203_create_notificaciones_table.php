<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificacionesTable extends Migration
{
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade'); // Ajusta según tu tabla de usuarios

            $table->unsignedBigInteger('elemento_id')->nullable();
            $table->foreign('elemento_id')->references('id')->on('elementos')->onDelete('cascade');

            $table->unsignedBigInteger('cuenta_id')->nullable();
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('cascade');

            $table->string('tipo', 50);
            $table->string('descripcion', 255);
            $table->boolean('leido')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
}
