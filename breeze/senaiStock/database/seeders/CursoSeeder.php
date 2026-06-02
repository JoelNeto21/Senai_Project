<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Desenvolvimento de Sistemas',
            'Administracao',
            'Eletroeletronica',
        ])->each(fn (string $curso) => Curso::firstOrCreate(['nome_curso' => $curso]));
    }
}
