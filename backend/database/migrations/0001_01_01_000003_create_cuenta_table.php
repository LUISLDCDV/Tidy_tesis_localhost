<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuentaTable extends Migration
{
    public function up()
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('id_medio_pago')->nullable();
            $table->foreign('id_medio_pago')->references('id')->on('medios_de_pago')->onDelete('set null');

            $table->json('configuraciones')->nullable();
            $table->integer('total_xp')->default(0); // Columna de XP
            $table->integer('current_level')->default(0); // Columna de nivel actual
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('cuentas');
    }
}
