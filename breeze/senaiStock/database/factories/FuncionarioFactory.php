<?php

namespace Database\Factories;

use App\Models\Funcionario;
use App\Models\Cargo;
use Illuminate\Database\Eloquent\Factories\Factory;

class FuncionarioFactory extends Factory
{
    protected $model = Funcionario::class;

    public function definition(): array
    {
        return [
            'NIF' => $this->faker->unique()->numerify('##########'),
            'Nome' => $this->faker->name(),
            'Cpf' => $this->faker->unique()->numerify('###########'),
            'Id_cargo_FK' => Cargo::factory(),
        ];
    }
}
