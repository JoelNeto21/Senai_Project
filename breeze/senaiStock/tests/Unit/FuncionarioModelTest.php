<?php

namespace Tests\Unit;

use App\Models\Funcionario;
use App\Models\Cargo;
use Tests\TestCase;

class FuncionarioModelTest extends TestCase
{
    /**
     * Test that Funcionario has cargo relationship
     */
    public function test_funcionario_has_cargo_relationship(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();
        $funcionario = Funcionario::factory()->create(['Id_cargo_FK' => $cargo->Id_cargo]);

        // Act
        $cargoFromRelation = $funcionario->cargo;

        // Assert
        $this->assertNotNull($cargoFromRelation);
        $this->assertEquals($cargo->Id_cargo, $cargoFromRelation->Id_cargo);
        $this->assertEquals($cargo->Nome_cargo, $cargoFromRelation->Nome_cargo);
    }

    /**
     * Test that Funcionario model has fillable attributes
     */
    public function test_funcionario_fillable_attributes(): void
    {
        // Arrange
        $funcionarioData = [
            'NIF' => '1234567890',
            'Nome' => 'Test User',
            'Cpf' => '12345678901',
            'Id_cargo_FK' => Cargo::factory()->create()->Id_cargo,
        ];

        // Act
        $funcionario = Funcionario::create($funcionarioData);

        // Assert
        $this->assertEquals('Test User', $funcionario->Nome);
        $this->assertEquals('12345678901', $funcionario->Cpf);
    }

    /**
     * Test that Funcionario uses custom primary key
     */
    public function test_funcionario_custom_primary_key(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create();

        // Act
        $retrieved = Funcionario::find($funcionario->Id_funcionario);

        // Assert
        $this->assertNotNull($retrieved);
        $this->assertEquals($funcionario->Id_funcionario, $retrieved->Id_funcionario);
    }

    /**
     * Test that multiple funcionarios can exist
     */
    public function test_multiple_funcionarios_can_be_created(): void
    {
        // Act
        $funcionarios = Funcionario::factory()->count(5)->create();

        // Assert
        $this->assertCount(5, $funcionarios);
        $this->assertCount(5, Funcionario::all());
    }

    /**
     * Test that funcionario can be updated
     */
    public function test_funcionario_can_be_updated(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create(['Nome' => 'Original Name']);

        // Act
        $funcionario->update(['Nome' => 'Updated Name']);

        // Assert
        $this->assertEquals('Updated Name', $funcionario->fresh()->Nome);
    }

    /**
     * Test that funcionario can be deleted
     */
    public function test_funcionario_can_be_deleted(): void
    {
        // Arrange
        $funcionario = Funcionario::factory()->create();
        $id = $funcionario->Id_funcionario;

        // Act
        $funcionario->delete();

        // Assert
        $this->assertNull(Funcionario::find($id));
    }
}
