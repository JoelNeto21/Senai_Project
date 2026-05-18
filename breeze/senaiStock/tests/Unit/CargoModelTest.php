<?php

namespace Tests\Unit;

use App\Models\Cargo;
use App\Models\Funcionario;
use Tests\TestCase;

class CargoModelTest extends TestCase
{
    /**
     * Test that Cargo has many Funcionarios relationship
     */
    public function test_cargo_has_many_funcionarios(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();
        $funcionarios = Funcionario::factory()->count(3)->create(['Id_cargo_FK' => $cargo->Id_cargo]);

        // Act
        $cargoFuncionarios = $cargo->funcionarios;

        // Assert
        $this->assertCount(3, $cargoFuncionarios);
        foreach ($cargoFuncionarios as $funcionario) {
            $this->assertEquals($cargo->Id_cargo, $funcionario->Id_cargo_FK);
        }
    }

    /**
     * Test that Cargo model has fillable attributes
     */
    public function test_cargo_fillable_attributes(): void
    {
        // Arrange
        $cargoData = [
            'Nome_cargo' => 'Test Cargo',
        ];

        // Act
        $cargo = Cargo::create($cargoData);

        // Assert
        $this->assertEquals('Test Cargo', $cargo->Nome_cargo);
    }

    /**
     * Test that Cargo uses custom primary key
     */
    public function test_cargo_custom_primary_key(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();

        // Act
        $retrieved = Cargo::find($cargo->Id_cargo);

        // Assert
        $this->assertNotNull($retrieved);
        $this->assertEquals($cargo->Id_cargo, $retrieved->Id_cargo);
    }

    /**
     * Test that cargo can be updated
     */
    public function test_cargo_can_be_updated(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create(['Nome_cargo' => 'Original Name']);

        // Act
        $cargo->update(['Nome_cargo' => 'Updated Name']);

        // Assert
        $this->assertEquals('Updated Name', $cargo->fresh()->Nome_cargo);
    }

    /**
     * Test that cargo can be deleted
     */
    public function test_cargo_can_be_deleted(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();
        $id = $cargo->Id_cargo;

        // Act
        $cargo->delete();

        // Assert
        $this->assertNull(Cargo::find($id));
    }

    /**
     * Test that multiple cargos can exist
     */
    public function test_multiple_cargos_can_be_created(): void
    {
        // Arrange - 3 already exist from setUp
        $newCargos = Cargo::factory()->count(3)->create();

        // Act
        $totalCargos = Cargo::all()->count();

        // Assert
        $this->assertCount(6, Cargo::all()); // 3 from setUp + 3 new
    }

    /**
     * Test that funcionarios are deleted when cargo relationship exists
     */
    public function test_funcionarios_relationship_on_cargo(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();
        $funcionario = Funcionario::factory()->create(['Id_cargo_FK' => $cargo->Id_cargo]);

        // Act & Assert
        $this->assertTrue($cargo->funcionarios->contains($funcionario));
    }
}
