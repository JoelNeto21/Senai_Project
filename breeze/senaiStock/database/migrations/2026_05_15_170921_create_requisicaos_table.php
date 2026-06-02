<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisicoes', function (Blueprint $table) {
            $table->id();

            $table->date('data_requisicao');

            // FK Livro
            $table->foreignId('livro_id')
                ->constrained('livros')
                ->onDelete('cascade');
            $table->string('titulo_livro');
            $table->integer('quantidade_livro');

            // FK Turma
            $table->foreignId('turma_id')
                ->constrained('turmas')
                ->onDelete('cascade');
            $table->string('nome_turma');

            // FK Funcionário
            $table->unsignedBigInteger('funcionario_id');

            $table->foreign('funcionario_id')
                ->references('Id_funcionario')
                ->on('funcionarios')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicoes');
    }
};
