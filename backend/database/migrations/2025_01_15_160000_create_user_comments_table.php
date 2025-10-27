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
        Schema::create('user_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Puede ser nullable para usuarios anónimos
            $table->string('email')->nullable(); // Email para usuarios anónimos
            $table->string('name')->nullable(); // Nombre para usuarios anónimos
            $table->enum('type', ['help_request', 'suggestion', 'bug_report', 'feedback', 'other'])->default('help_request');
            $table->string('subject', 200); // Asunto del comentario
            $table->text('comment'); // El comentario/solicitud
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->text('admin_response')->nullable(); // Respuesta del administrador
            $table->unsignedBigInteger('responded_by')->nullable(); // ID del admin que respondió
            $table->timestamp('responded_at')->nullable(); // Fecha de respuesta
            $table->json('metadata')->nullable(); // Datos adicionales (user_agent, ip, etc.)
            $table->timestamps();

            // Índices
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('responded_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_comments');
    }
};