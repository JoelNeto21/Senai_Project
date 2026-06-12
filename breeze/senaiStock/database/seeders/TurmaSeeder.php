<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Turma;
use Illuminate\Database\Seeder;

class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        $desenvolvimento = Curso::firstOrCreate(['nome_curso' => 'Desenvolvimento de Sistemas']);
        $administracao = Curso::firstOrCreate(['nome_curso' => 'Administração']);
        $eletro = Curso::firstOrCreate(['nome_curso' => 'Eletroeletrônica']);

        collect([
            ['nome_turma' => 'DS-1A', 'curso_id' => $desenvolvimento->id],
            ['nome_turma' => 'DS-2B', 'curso_id' => $desenvolvimento->id],
            ['nome_turma' => 'ADM-1A', 'curso_id' => $administracao->id],
            ['nome_turma' => 'ELE-3C', 'curso_id' => $eletro->id],
        ])->each(fn (array $turma) => Turma::firstOrCreate(
            ['nome_turma' => $turma['nome_turma']],
            ['curso_id' => $turma['curso_id']]
        ));
    }
}
