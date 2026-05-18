<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Funcionario;
use Tests\TestCase;

class CargoFeatureTest extends TestCase
{
    /**
     * Test that index view displays all cargos
     */
    public function test_index_shows_all_cargos(): void
    {
        // Arrange - setUp already creates 3 cargos
        $additionalCargos = Cargo::factory()->count(2)->create();

        // Act
        $response = $this->get(route('cargos.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('cargos');
        
        // Check all cargos are in the view
        $cargos = $response->viewData('cargos');
        $this->assertCount(5, $cargos); // 3 from setUp + 2 created
    }

    /**
     * Test that index shows cargo names in table
     */
    public function test_index_displays_cargo_names(): void
    {
        // Arrange
        $cargo1 = Cargo::factory()->create(['Nome_cargo' => 'Gerente']);
        $cargo2 = Cargo::factory()->create(['Nome_cargo' => 'Assistente']);

        // Act
        $response = $this->get(route('cargos.index'));

        // Assert
        $response->assertSee('Gerente');
        $response->assertSee('Assistente');
    }

    /**
     * Test store creates cargo with valid data
     */
    public function test_store_creates_cargo_with_valid_data(): void
    {
        // Arrange
        $cargoData = [
            'Nome_cargo' => 'Diretor',
        ];

        // Act
        $response = $this->post(route('cargos.store'), $cargoData);

        // Assert
        $response->assertRedirect(route('cargos.index'));
        $response->assertSessionHas('success', 'Cargo criado com sucesso!');
        
        // Check database
        $this->assertDatabaseHas('cargos', [
            'Nome_cargo' => 'Diretor',
        ]);
    }

    /**
     * Test store fails with duplicate cargo name
     */
    public function test_store_fails_with_duplicate_name(): void
    {
        // Arrange
        Cargo::factory()->create(['Nome_cargo' => 'Supervisor']);
        
        $cargoData = [
            'Nome_cargo' => 'Supervisor', // Duplicate
        ];

        // Act
        $response = $this->post(route('cargos.store'), $cargoData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Nome_cargo');
    }

    /**
     * Test store fails with missing Nome_cargo
     */
    public function test_store_fails_with_missing_nome_cargo(): void
    {
        // Arrange
        $cargoData = [
            // Missing Nome_cargo
        ];

        // Act
        $response = $this->post(route('cargos.store'), $cargoData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Nome_cargo');
    }

    /**
     * Test store fails with empty string
     */
    public function test_store_fails_with_empty_string(): void
    {
        // Arrange
        $cargoData = [
            'Nome_cargo' => '',
        ];

        // Act
        $response = $this->post(route('cargos.store'), $cargoData);

        // Assert
        $response->assertStatus(422);
        $response->assertSessionHasErrors('Nome_cargo');
    }

    /**
     * Test destroy deletes cargo
     */
    public function test_destroy_deletes_cargo(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();
        $cargoId = $cargo->Id_cargo;

        // Act
        $response = $this->delete(route('cargos.destroy', $cargo));

        // Assert
        $response->assertRedirect(route('cargos.index'));
        $response->assertSessionHas('success', 'Cargo removido.');
        
        // Check database
        $this->assertDatabaseMissing('cargos', [
            'Id_cargo' => $cargoId,
        ]);
    }

    /**
     * Test destroy redirects to index with success message
     */
    public function test_destroy_shows_success_message(): void
    {
        // Arrange
        $cargo = Cargo::factory()->create();

        // Act
        $this->delete(route('cargos.destroy', $cargo));
        $response = $this->get(route('cargos.index'));

        // Assert
        $response->assertSee('Cargo removido.');
    }

    /**
     * Test that multiple cargos can be created and deleted
     */
    public function test_multiple_cargo_operations(): void
    {
        // Arrange - 3 cargos already exist from setUp
        $initialCount = Cargo::count();

        // Act - Create new cargo
        $this->post(route('cargos.store'), ['Nome_cargo' => 'Novo Cargo']);
        $this->assertEquals($initialCount + 1, Cargo::count());

        // Get the new cargo
        $newCargo = Cargo::where('Nome_cargo', 'Novo Cargo')->first();

        // Act - Delete it
        $this->delete(route('cargos.destroy', $newCargo));

        // Assert
        $this->assertEquals($initialCount, Cargo::count());
    }

    /**
     * Test that cargo name is case-sensitive for uniqueness
     */
    public function test_cargo_name_is_case_sensitive(): void
    {
        // Arrange
        Cargo::factory()->create(['Nome_cargo' => 'Director']);
        
        $cargoData = [
            'Nome_cargo' => 'director', // Different case
        ];

        // Act
        $response = $this->post(route('cargos.store'), $cargoData);

        // Assert - Depending on database collation, this might pass or fail
        // But should be allowed by validation (case-sensitive)
        // This test depends on database configuration
        $response->assertRedirect(route('cargos.index'));
    }

    /**
     * Test that max length is enforced (255 characters)
     */
    public function test_store_fails_with_exceeding_max_length(): void
    {
        // Arrange
        $cargoData = [
            'Nome_cargo' => str_repeat('a', 256), // 256 characters (exceeds max 255)
        ];

        // Act
        $response = $this->post(route('cargos.store'), $cargoData);

        // Assert
        $response->assertStatus(422);
    }
}
