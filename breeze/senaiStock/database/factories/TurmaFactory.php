<?php

namespace Database\Factories;

use App\Models\Turma;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

class TurmaFactory extends Factory
{
    protected $model = Turma::class;

    public function definition(): array
    {
        return [
            'nome_turma' => $this->faker->unique()->word() . ' ' . $this->faker->word(),
            'curso_id' => Curso::factory(),
        ];
    }
}
