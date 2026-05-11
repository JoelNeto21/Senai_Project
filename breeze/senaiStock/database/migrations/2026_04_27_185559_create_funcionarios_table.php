<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcionarios', function (Blueprint $table) {

            $table->id('Id_funcionario');

            $table->integer('NIF');

            $table->string('Nome');

            $table->string('Cpf', 14);

            $table->unsignedBigInteger('Id_cargo_FK');

            $table->foreign('Id_cargo_FK')
                  ->references('Id_cargo')
                  ->on('cargos');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};