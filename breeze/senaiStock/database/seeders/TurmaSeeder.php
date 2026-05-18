<?php

namespace Database\Seeders;

use App\Models\Turma;
use Illuminate\Database\Seeder;

class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        Turma::create(['nome_turma' => 'Turma 101 - Manhã', 'curso_id' => 1]);
        Turma::create(['nome_turma' => 'Turma 102 - Tarde', 'curso_id' => 1]);
        Turma::create(['nome_turma' => 'Turma 201 - Noite', 'curso_id' => 3]);
    }
}