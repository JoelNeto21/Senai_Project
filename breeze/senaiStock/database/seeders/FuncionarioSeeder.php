<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Database\Seeder;

class FuncionarioSeeder extends Seeder
{
    public function run(): void
    {
        $coordenador = Cargo::where('Nome_cargo', 'Coordenador')->firstOrFail();
        $professor = Cargo::where('Nome_cargo', 'Professor')->firstOrFail();

        Funcionario::updateOrCreate(['NIF' => 111111], [
            'Nome' => 'Coordenador Senai',
            'Cpf' => '11111111111',
            'password' => 'senai123',
            'Id_cargo_FK' => $coordenador->Id_cargo,
        ]);

        Funcionario::updateOrCreate(['NIF' => 654321], [
            'Nome' => 'Professor Teste',
            'Cpf' => '98765432100',
            'password' => 'senai123',
            'Id_cargo_FK' => $professor->Id_cargo,
        ]);
    }
}
