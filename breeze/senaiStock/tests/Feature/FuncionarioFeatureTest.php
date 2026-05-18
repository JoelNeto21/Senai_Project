<?php

namespace Tests\Feature;

use App\Models\Funcionario;
use App\Models\Cargo;
use Tests\TestCase;

class FuncionarioFeatureTest extends TestCase
{
    /**
     * Test that index view displays all funcionarios
     */
    public function test_index_shows_all_funcionarios(): void
    {
        // Arrange
        $funcionarios = Funcionario::factory()->count(3)->create();

        // Act
        $response = $this->get(route('funcionarios.index'));

        // Assert
        $response->assertStatus(200);
        
        // Check if view is displayed
        $response->assertViewHas('funcionarios');
        
        // Check if all funcionarios are shown in view
        foreach ($funcionarios as $funcionario) {
            $response->assertSee($funcionario->Nome);
            $response->assertSee($funcionario->Cpf);
            $response->assertSee($funcionario->cargo->Nome_cargo);
        }
    }

    /**
     * Test that create view shows form with all cargos
     */
    public function test_create_shows_form_with_cargos(): void
    {
        // Act
        $response = $this->get(route('funcionarios.create'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('cargos');
        
        // Verify cargos count (3 were created in setUp)
        $cargos = $response->viewData('cargos');
        $this->assertCount(3, $cargos);
        
        // Check form is present
        $response->assertSee('name="Nome"');
        $response->assertSee('name="Cpf"');
        $response->assertSee('name="Id_cargo_FK"');
    }

    /**
     * Test store creates funcionario with valid data
     */
    public function test_store_creates_funcionario_with_valid_data(): void
    {
        // Arrange
        $cargo = Cargo::first();
        $funcionarioData = [
            'Nome' => 'João da Silva',
            'Cpf' => '12345678901',
            'Id_cargo_FK' => $cargo->Id_cargo,
        ];

        // Act
        $response = $this->post(route('funcionarios.store'), $funcionarioData);

        // Assert
        $response->assertRedirect(route('funcionarios.index'));
        $response->assertSessionHas('success', 'Funcionário cadastrado com sucesso!');
        
        // Check database
        $this->assertDatabaseHas('funcionarios', [
            'Nome' => 'João da Silva',
            'Cpf' => '12345678901',
            'Id_cargo_FK' => $cargo->Id_cargo,
        ]);
    }

    /**
     * Test store fails with duplicate CPF
     */
    public function test_store_fails_with_duplicate_cpf(): void
    {
        // Arrange
        $existingFuncionario = Funcionario::factory()->create(['Cpf' => '11111111111']);
        $cargo = Cargo::first();
        
        $funcionarioData = [
            'Nome' => 'Maria Silva',
            'Cpf' => '11111111111', // Same CPF
            'Id_cargo_FK' => $cargo->Id_cargo,
        ];

        // Act
        $response = $this->post(route('funcionarios.store'), $funcionarioData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Cpf');
        
        // Check database - should still have only one
        $this->assertEquals(2, Funcionario::count()); // Original + one attempt
    }

    /**
     * Test store fails with missing Nome
     */
    public function test_store_fails_with_missing_nome(): void
    {
        // Arrange
        $cargo = Cargo::first();
        $funcionarioData = [
            'Cpf' => '99999999999',
            'Id_cargo_FK' => $cargo->Id_cargo,
            // Missing Nome
        ];

        // Act
        $response = $this->post(route('funcionarios.store'), $funcionarioData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Nome');
    }

    /**
     * Test store fails with missing Cpf
     */
    public function test_store_fails_with_missing_cpf(): void
    {
        // Arrange
        $cargo = Cargo::first();
        $funcionarioData = [
            'Nome' => 'João Silva',
            'Id_cargo_FK' => $cargo->Id_cargo,
            // Missing Cpf
        ];

        // Act
        $response = $this->post(route('funcionarios.store'), $funcionarioData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Cpf');
    }

    /**
     * Test store fails with invalid cargo
     */
    public function test_store_fails_with_invalid_cargo(): void
    {
        // Arrange
        $funcionarioData = [
            'Nome' => 'João Silva',
            'Cpf' => '12345678901',
            'Id_cargo_FK' => 999, // Non-existent cargo ID
        ];

        // Act
        $response = $this->post(route('funcionarios.store'), $funcionarioData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Id_cargo_FK');
    }

    /**
     * Test edit shows form with prefilled data
     */
    public function test_edit_shows_form_prefilled(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create();

        // Act
        $response = $this->get(route('funcionarios.edit', $funcionario));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('funcionario', $funcionario);
        $response->assertViewHas('cargos');
        
        // Check if values are in form
        $response->assertSee($funcionario->Nome);
        $response->assertSee($funcionario->Cpf);
    }

    /**
     * Test update modifies funcionario successfully
     */
    public function test_update_modifies_funcionario(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create();
        $newCargo = Cargo::factory()->create();
        
        $updateData = [
            'Nome' => 'Nome Atualizado',
            'Cpf' => '55555555555',
            'Id_cargo_FK' => $newCargo->Id_cargo,
        ];

        // Act
        $response = $this->put(route('funcionarios.update', $funcionario), $updateData);

        // Assert
        $response->assertRedirect(route('funcionarios.index'));
        $response->assertSessionHas('success', 'Dados atualizados!');
        
        // Check database
        $this->assertDatabaseHas('funcionarios', [
            'Id_funcionario' => $funcionario->Id_funcionario,
            'Nome' => 'Nome Atualizado',
            'Cpf' => '55555555555',
            'Id_cargo_FK' => $newCargo->Id_cargo,
        ]);
    }

    /**
     * Test update allows same CPF for same employee
     */
    public function test_update_allows_same_cpf_for_same_employee(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create(['Cpf' => '44444444444']);
        $originalCpf = $funcionario->Cpf;
        
        $updateData = [
            'Nome' => 'Nome Novo',
            'Cpf' => $originalCpf, // Same CPF
            'Id_cargo_FK' => $funcionario->Id_cargo_FK,
        ];

        // Act
        $response = $this->put(route('funcionarios.update', $funcionario), $updateData);

        // Assert
        $response->assertRedirect(route('funcionarios.index'));
        $this->assertDatabaseHas('funcionarios', [
            'Id_funcionario' => $funcionario->Id_funcionario,
            'Cpf' => $originalCpf,
        ]);
    }

    /**
     * Test update fails with duplicate CPF from another employee
     */
    public function test_update_fails_with_duplicate_cpf_from_another(): void
    {
        // Arrange
        $funcionario1 = Funcionario::factory()->create(['Cpf' => '11111111111']);
        $funcionario2 = Funcionario::factory()->create(['Cpf' => '22222222222']);
        
        $updateData = [
            'Nome' => 'Updated Name',
            'Cpf' => '11111111111', // CPF from funcionario1
            'Id_cargo_FK' => $funcionario2->Id_cargo_FK,
        ];

        // Act
        $response = $this->put(route('funcionarios.update', $funcionario2), $updateData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Cpf');
    }

    /**
     * Test destroy deletes funcionario
     */
    public function test_destroy_deletes_funcionario(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create();
        $funcionarioId = $funcionario->Id_funcionario;

        // Act
        $response = $this->delete(route('funcionarios.destroy', $funcionario));

        // Assert
        $response->assertRedirect(route('funcionarios.index'));
        $response->assertSessionHas('success', 'Funcionário removido.');
        
        // Check database
        $this->assertDatabaseMissing('funcionarios', [
            'Id_funcionario' => $funcionarioId,
        ]);
    }

    /**
     * Test index shows success message from session
     */
    public function test_index_shows_success_message(): void
    {
        // Arrange
        $this->post(route('funcionarios.store'), [
            'Nome' => 'Test Funcionario',
            'Cpf' => '33333333333',
            'Id_cargo_FK' => Cargo::first()->Id_cargo,
        ]);

        // Act
        $response = $this->get(route('funcionarios.index'));

        // Assert
        $response->assertSee('Funcionário cadastrado com sucesso!');
    }
}
