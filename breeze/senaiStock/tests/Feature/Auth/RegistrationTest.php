<?php

namespace Tests\Feature\Auth;

use App\Models\Cargo;
use App\Models\Funcionario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('employee.register'));

        $response->assertOk()
            ->assertSee('Criar conta de Professor')
            ->assertDontSee('name="Id_cargo_FK"', false);
    }

    public function test_public_registration_creates_only_a_professor(): void
    {
        $coordenador = Cargo::firstOrCreate(['Nome_cargo' => 'Coordenador']);

        $response = $this->post(route('employee.register.store'), [
            'Nome' => 'Prof. Teste',
            'NIF' => 987654,
            'password' => 'password',
            'password_confirmation' => 'password',
            'Id_cargo_FK' => $coordenador->Id_cargo,
        ]);

        $funcionario = Funcionario::with('cargo')->where('NIF', 987654)->firstOrFail();

        $this->assertSame('Professor', $funcionario->cargo->Nome_cargo);
        $this->assertTrue(Hash::check('password', $funcionario->password));
        $response->assertRedirect(route('employee.login'));
    }

    public function test_legacy_register_url_also_creates_a_professor(): void
    {
        $this->post('/register', [
            'Nome' => 'Prof. Legado',
            'NIF' => 987655,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('employee.login'));

        $this->assertDatabaseHas('funcionarios', [
            'Nome' => 'Prof. Legado',
            'NIF' => 987655,
        ]);
    }

    public function test_only_a_coordenador_can_create_another_coordenador(): void
    {
        $coordenador = Cargo::firstOrCreate(['Nome_cargo' => 'Coordenador']);
        $payload = [
            'Nome' => 'Novo Coordenador',
            'NIF' => 987656,
            'Id_cargo_FK' => $coordenador->Id_cargo,
        ];

        $this->withEmployeeSession('Professor')
            ->post(route('funcionarios.store'), $payload)
            ->assertForbidden();

        $this->withEmployeeSession('Coordenador')
            ->post(route('funcionarios.store'), $payload)
            ->assertRedirect(route('senai.dashboard', ['view' => 'people']));

        $this->assertDatabaseHas('funcionarios', [
            'NIF' => 987656,
            'Id_cargo_FK' => $coordenador->Id_cargo,
        ]);
    }
}
