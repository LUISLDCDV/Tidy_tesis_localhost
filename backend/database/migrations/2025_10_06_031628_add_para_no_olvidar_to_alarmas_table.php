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
            $table->text('para_no_olvidar')->nullable()->after('informacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alarmas', function (Blueprint $table) {
            $table->dropColumn('para_no_olvidar');
        });
    }
};
