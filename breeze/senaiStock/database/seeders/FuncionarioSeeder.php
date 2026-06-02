<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Database\Seeder;

class FuncionarioSeeder extends Seeder
{
    public function run(): void
    {
        $administrador = Cargo::where('Nome_cargo', 'Administrador')->firstOrFail();
        $almoxarife = Cargo::where('Nome_cargo', 'Almoxarife')->firstOrFail();
        $professor = Cargo::where('Nome_cargo', 'Professor')->firstOrFail();

        Funcionario::firstOrCreate(['NIF' => 111111], [
            'Nome' => 'Administrador Senai',
            'Cpf' => '11111111111',
            'Id_cargo_FK' => $administrador->Id_cargo,
        ]);

        Funcionario::firstOrCreate(['NIF' => 123456], [
            'NIF' => 123456,
            'Nome' => 'Almoxarifado Senai',
            'Cpf' => '12345678900',
            'Id_cargo_FK' => $almoxarife->Id_cargo,
        ]);

        Funcionario::firstOrCreate(['NIF' => 654321], [
            'NIF' => 654321,
            'Nome' => 'Professor Teste',
            'Cpf' => '98765432100',
            'Id_cargo_FK' => $professor->Id_cargo,
        ]);
    }
}
