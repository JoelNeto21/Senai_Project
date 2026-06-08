<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_requests', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_name');
            $table->string('teacher_email')->nullable();
            $table->string('class_name');
            $table->string('subject')->nullable();
            $table->foreignId('book_id')
                ->nullable()
                ->constrained('books')
                ->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('quantity');
            $table->string('status')->default('pendente');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_requests');
    }
};