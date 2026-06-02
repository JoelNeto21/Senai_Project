<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_request_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_request_id')
                ->constrained('teacher_requests')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('funcionario_id')->nullable();
            $table->string('sender_type')->default('sistema');
            $table->string('sender_name')->nullable();
            $table->string('status')->nullable();
            $table->text('message');
            $table->boolean('email_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('funcionario_id')
                ->references('Id_funcionario')
                ->on('funcionarios')
                ->nullOnDelete();
            $table->index(['teacher_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_request_messages');
    }
};
