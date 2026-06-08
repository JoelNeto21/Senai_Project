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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['entrada', 'saida']);
            $table->foreignId('book_id')
                ->constrained('books')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedBigInteger('funcionario_id')
                ->nullable();
            $table->unsignedInteger('quantity');
            $table->text('justification')->nullable();
            $table->timestamps();

            // Foreign key para funcionarios com custom naming
            $table->foreign('funcionario_id')
                ->references('Id_funcionario')
                ->on('funcionarios')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};