<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Database\Seeder;

class FuncionarioSeeder extends Seeder
{
    public function run(): void
    {
        $almoxarife = Cargo::where('Nome_cargo', 'Almoxarife')->firstOrFail();
        $professor = Cargo::where('Nome_cargo', 'Professor')->firstOrFail();

        Funcionario::create([
            'NIF' => 123456,
            'Nome' => 'Almoxarifado Senai',
            'Cpf' => '12345678900',
            'Id_cargo_FK' => $almoxarife->Id_cargo,
        ]);

        Funcionario::create([
            'NIF' => 654321,
            'Nome' => 'Professor Teste',
            'Cpf' => '98765432100',
            'Id_cargo_FK' => $professor->Id_cargo,
        ]);
    }
}