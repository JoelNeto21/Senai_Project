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

            // CORREÇÃO: Removido 'titulo_livro' e alterado para referenciar 'books' em vez de 'livros'
            $table->foreignId('book_id')
                ->constrained('books')
                ->onDelete('cascade');
            
            $table->integer('quantidade_livro');

            // CORREÇÃO: Removido 'nome_turma'. O ID já é suficiente para obter o nome via Eloquent.
            $table->foreignId('turma_id')
                ->constrained('turmas')
                ->onDelete('cascade');

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