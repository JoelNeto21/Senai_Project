<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Funcionario;
use Tests\TestCase;

class EmployeePermissionTest extends TestCase
{
    public function test_professor_cannot_access_internal_panel_login(): void
    {
        $cargo = Cargo::factory()->create(['Nome_cargo' => 'Professor']);
        Funcionario::factory()->create([
            'NIF' => 654321,
            'Cpf' => '98765432100',
            'Id_cargo_FK' => $cargo->Id_cargo,
        ]);

        $response = $this->post(route('employee.authenticate'), [
            'nif' => 654321,
            'cpf' => '98765432100',
        ]);

        $response->assertSessionHasErrors('nif');
        $response->assertSessionMissing('employee.id');
    }

    public function test_almoxarife_cannot_open_people_management(): void
    {
        $response = $this
            ->withSession([
                'employee' => [
                    'id' => 10,
                    'name' => 'Almoxarifado Senai',
                    'cargo' => 'Almoxarife',
                    'role_key' => 'almoxarife',
                ],
            ])
            ->get(route('senai.dashboard', ['view' => 'people']));

        $response->assertForbidden();
    }

    public function test_almoxarife_cannot_open_classroom_management(): void
    {
        $response = $this
            ->withSession([
                'employee' => [
                    'id' => 10,
                    'name' => 'Almoxarifado Senai',
                    'cargo' => 'Almoxarife',
                    'role_key' => 'almoxarife',
                ],
            ])
            ->get(route('senai.dashboard', ['view' => 'classes']));

        $response->assertForbidden();
    }

    public function test_almoxarife_navigation_hides_administrative_links(): void
    {
        $response = $this
            ->withSession([
                'employee' => [
                    'id' => 10,
                    'name' => 'Almoxarifado Senai',
                    'cargo' => 'Almoxarife',
                    'role_key' => 'almoxarife',
                ],
            ])
            ->get(route('senai.dashboard', ['view' => 'insights']));

        $response->assertOk();
        $response->assertSee('Fornecedores');
        $response->assertSee('dashboard/suppliers', false);
        $response->assertDontSee('dashboard/classes', false);
        $response->assertDontSee('dashboard/people', false);
        $response->assertDontSee('dashboard/settings', false);
    }
}
