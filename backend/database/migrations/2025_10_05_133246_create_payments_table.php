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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('payment_id')->unique()->comment('ID del pago en MercadoPago');
                $table->string('collection_id')->nullable()->comment('ID de la colección');
                $table->string('subscription_id')->nullable()->comment('ID de la suscripción');
                $table->string('status')->default('pending')->comment('approved, pending, rejected, cancelled');
                $table->string('payment_type')->comment('subscription, one_time');
                $table->string('payment_method')->nullable()->comment('credit_card, debit_card, etc');
                $table->decimal('amount', 10, 2)->comment('Monto pagado');
                $table->string('currency', 3)->default('ARS')->comment('Moneda del pago');
                $table->string('plan_type')->nullable()->comment('monthly, annual');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable()->comment('Datos adicionales de MercadoPago');
                $table->timestamp('paid_at')->nullable()->comment('Fecha de pago efectivo');
                $table->timestamps();

                // Índices
                $table->index('user_id');
                $table->index('status');
                $table->index('payment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
