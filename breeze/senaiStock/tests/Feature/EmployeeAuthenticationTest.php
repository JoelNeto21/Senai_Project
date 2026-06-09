<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Funcionario;
use Database\Seeders\CargoSeeder;
use Database\Seeders\FuncionarioSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeAuthenticationTest extends TestCase
{
    public function test_all_seeded_employees_can_log_in(): void
    {
        $this->seed([CargoSeeder::class, FuncionarioSeeder::class]);

        foreach ([
            111111 => 'insights',
            654321 => 'teacher_requests',
        ] as $nif => $view) {
            $response = $this->post(route('employee.authenticate'), [
                'nif' => $nif,
                'password' => 'senai123',
            ]);

            $response->assertRedirect(route('senai.dashboard', ['view' => $view]));
            $this->get(route('senai.dashboard', ['view' => $view]))->assertOk();
        }
    }

    public function test_role_seed_contains_only_the_two_supported_roles(): void
    {
        $unsupported = Cargo::factory()->create(['Nome_cargo' => 'Administrador']);
        Funcionario::factory()->create(['Id_cargo_FK' => $unsupported->Id_cargo]);

        $this->seed(CargoSeeder::class);

        $this->assertSame(
            ['Coordenador', 'Professor'],
            Cargo::orderBy('Nome_cargo')->pluck('Nome_cargo')->all()
        );
        $this->assertSame(0, Funcionario::whereDoesntHave('cargo')->count());
    }

    public function test_funcionario_password_mutator_accepts_an_existing_hash(): void
    {
        $cargo = Cargo::factory()->create(['Nome_cargo' => 'Coordenador']);
        $funcionario = Funcionario::factory()->create([
            'Id_cargo_FK' => $cargo->Id_cargo,
            'password' => Hash::make('senai123'),
        ]);

        $this->assertTrue(Hash::check('senai123', $funcionario->password));
    }
}
