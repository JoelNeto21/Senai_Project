<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cargo;
use App\Models\Funcionario;
use Tests\TestCase;

class EmployeePermissionTest extends TestCase
{
    public function test_professor_can_access_internal_panel_login(): void
    {
        $cargo = Cargo::factory()->create(['Nome_cargo' => 'Professor']);
        Funcionario::factory()->create([
            'NIF' => 654321,
            'Cpf' => '98765432100',
            'password' => 'senai123',
            'Id_cargo_FK' => $cargo->Id_cargo,
        ]);

        $response = $this->post(route('employee.authenticate'), [
            'nif' => 654321,
            'password' => 'senai123',
        ]);

        $response->assertRedirect(route('senai.dashboard', ['view' => 'teacher_requests']));
        $response->assertSessionHas('employee.cargo', 'Professor');
    }

    public function test_professor_only_has_requests_navigation(): void
    {
        $response = $this->withEmployeeSession('Professor')
            ->get(route('senai.dashboard', ['view' => 'teacher_requests']));

        $response->assertOk();
        $this->assertSame(
            ['teacher_requests'],
            collect($response->viewData('navigationItems'))->pluck('id')->all()
        );
    }

    public function test_professor_cannot_access_catalog_or_administration(): void
    {
        $this->withEmployeeSession('Professor')
            ->get(route('senai.dashboard', ['view' => 'library']))
            ->assertForbidden();

        $this->withEmployeeSession('Professor')
            ->get(route('senai.dashboard', ['view' => 'people']))
            ->assertForbidden();
    }

    public function test_professor_cannot_call_coordinator_stock_actions_directly(): void
    {
        $book = Book::factory()->create();

        $this->withEmployeeSession('Professor')
            ->post(route('stock.alerts.purchase', $book))
            ->assertForbidden();

        $this->withEmployeeSession('Professor')
            ->postJson('/api/books', [
                'title' => 'Livro sem permissão',
                'isbn' => '9780000000001',
                'subject' => 'Área',
            ])
            ->assertForbidden();
    }
}
