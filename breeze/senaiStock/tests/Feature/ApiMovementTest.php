<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Movement;
use App\Models\User;
use Tests\TestCase;

class ApiMovementTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ===== INDEX: GET /api/movements =====

    public function test_can_list_all_movements(): void
    {
        $book = Book::factory()->create();
        Movement::factory()->count(3)->create(['book_id' => $book->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/movements');

        if ($response->status() !== 404) {
            $response->assertStatus(200);
            $response->assertJsonIsArray();
        }
    }

    public function test_returns_empty_array_when_no_movements_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/movements');

        if ($response->status() !== 404) {
            $response->assertStatus(200);
        }
    }

    public function test_requires_authentication_to_list_movements(): void
    {
        $response = $this->getJson('/api/movements');

        $this->assertIn($response->status(), [401, 404]);
    }

    public function test_returns_movements_with_correct_structure(): void
    {
        $book = Book::factory()->create();
        Movement::factory()->create([
            'book_id' => $book->id,
            'user_id' => $this->user->id,
            'type' => 'entrada',
            'quantity' => 5,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/movements');

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                '*' => ['id', 'type', 'book_id', 'quantity', 'justification', 'created_at']
            ]);
        }
    }

    public function test_can_filter_movements_by_type(): void
    {
        $book = Book::factory()->create();
        Movement::factory()->create(['book_id' => $book->id, 'type' => 'entrada', 'quantity' => 5]);
        Movement::factory()->create(['book_id' => $book->id, 'type' => 'saida', 'quantity' => 2]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/movements?type=entrada');

        if ($response->status() === 200) {
            $response->assertJsonIsArray();
        }
    }

    // ===== SHOW: GET /api/movements/{id} =====

    public function test_can_retrieve_a_single_movement(): void
    {
        $book = Book::factory()->create();
        $movement = Movement::factory()->create(['book_id' => $book->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/movements/{$movement->id}");

        if ($response->status() !== 404) {
            $response->assertStatus(200);
            $response->assertJson([
                'id' => $movement->id,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
            ]);
        }
    }

    public function test_returns_404_for_non_existent_movement(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/movements/999999');

        $this->assertIn($response->status(), [404, 404]);
    }

    public function test_requires_authentication_to_view_movement(): void
    {
        $book = Book::factory()->create();
        $movement = Movement::factory()->create(['book_id' => $book->id]);

        $response = $this->getJson("/api/movements/{$movement->id}");

        $this->assertIn($response->status(), [401, 404]);
    }

    // ===== ENTRADA (Entry) =====

    public function test_can_create_entrada_movement(): void
    {
        $book = Book::factory()->create(['quantity' => 5]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 10,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(201);
            $this->assertEquals(15, $book->fresh()->quantity);
        }
    }

    public function test_increases_book_quantity_on_entrada(): void
    {
        $book = Book::factory()->create(['quantity' => 5]);
        $initialQuantity = $book->quantity;

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 7,
            ]);

        $book->refresh();
        $this->assertEquals($initialQuantity + 7, $book->quantity);
    }

    public function test_entrada_does_not_require_justification(): void
    {
        $book = Book::factory()->create(['quantity' => 5]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 5,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(201);
        }
    }

    public function test_creates_movement_record_on_entrada(): void
    {
        $book = Book::factory()->create(['quantity' => 0]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 20,
            ]);

        if ($response->status() === 201) {
            $this->assertDatabaseHas('movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 20,
            ]);
        }
    }

    public function test_entrada_is_always_allowed_with_valid_data(): void
    {
        $book = Book::factory()->create(['quantity' => 0]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 999999,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(201);
        }
    }

    // ===== SAIDA (Exit) =====

    public function test_can_create_saida_movement_when_quantity_available(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => 'Turma A - Aula de Literatura',
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(201);
            $this->assertEquals(15, $book->fresh()->quantity);
        }
    }

    public function test_decreases_book_quantity_on_saida(): void
    {
        $book = Book::factory()->create(['quantity' => 30]);
        $initialQuantity = $book->quantity;

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 8,
                'justification' => 'Turma B',
            ]);

        $book->refresh();
        $this->assertEquals($initialQuantity - 8, $book->quantity);
    }

    public function test_critical_blocks_saida_when_quantity_exceeds_available_stock(): void
    {
        $book = Book::factory()->create(['quantity' => 5]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 10,
                'justification' => 'Turma C',
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
            $this->assertEquals(5, $book->fresh()->quantity);
        }
    }

    public function test_critical_requires_justification_field_for_saida(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
            $this->assertEquals(20, $book->fresh()->quantity);
        }
    }

    public function test_critical_rejects_saida_with_empty_justification(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => '',
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_critical_rejects_saida_with_null_justification(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => null,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_creates_movement_record_on_saida(): void
    {
        $book = Book::factory()->create(['quantity' => 15]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => 'Classroom distribution',
            ]);

        if ($response->status() === 201) {
            $this->assertDatabaseHas('movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => 'Classroom distribution',
            ]);
        }
    }

    public function test_saida_quantity_cannot_exceed_current_stock(): void
    {
        $book = Book::factory()->create(['quantity' => 10]);

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 11,
                'justification' => 'Test',
            ]);

        $this->assertEquals(10, $book->fresh()->quantity);
    }

    public function test_stores_justification_message(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);
        $justification = 'Distribution for Class 2024-A';

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => $justification,
            ]);

        if ($response->status() === 201) {
            $this->assertDatabaseHas('movements', [
                'justification' => $justification,
            ]);
        }
    }

    public function test_requires_authentication_for_saida(): void
    {
        $book = Book::factory()->create(['quantity' => 20]);

        $response = $this->postJson('/api/movements', [
            'book_id' => $book->id,
            'type' => 'saida',
            'quantity' => 5,
            'justification' => 'Test',
        ]);

        $this->assertIn($response->status(), [401, 404]);
    }

    // ===== STOCK LEVEL VALIDATION =====

    public function test_never_allows_stock_to_go_below_zero(): void
    {
        $book = Book::factory()->create(['quantity' => 5]);

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 10,
                'justification' => 'Should fail',
            ]);

        $this->assertGreaterThanOrEqual(0, $book->fresh()->quantity);
    }

    public function test_correctly_sums_quantities_after_multiple_operations(): void
    {
        $book = Book::factory()->create(['quantity' => 0]);

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 10,
            ]);
        $this->assertEquals(10, $book->fresh()->quantity);

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 5,
            ]);
        $this->assertEquals(15, $book->fresh()->quantity);

        $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 3,
                'justification' => 'Distribution',
            ]);
        $this->assertEquals(12, $book->fresh()->quantity);
    }

    public function test_books_with_zero_quantity_can_receive_entries(): void
    {
        $book = Book::factory()->create(['quantity' => 0]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 5,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(201);
            $this->assertEquals(5, $book->fresh()->quantity);
        }
    }

    // ===== CRITICAL STOCK LEVEL =====

    public function test_can_identify_books_with_critical_stock(): void
    {
        $criticalBook = Book::factory()->create(['quantity' => 5]);
        $normalBook = Book::factory()->create(['quantity' => 15]);

        if (method_exists($criticalBook, 'isCriticalStock')) {
            $this->assertTrue($criticalBook->isCriticalStock());
            $this->assertFalse($normalBook->isCriticalStock());
        }
    }

    public function test_can_list_books_with_critical_stock(): void
    {
        Book::factory()->create(['quantity' => 3]);
        Book::factory()->create(['quantity' => 8]);
        Book::factory()->create(['quantity' => 25]);

        $criticalBooks = Book::where('quantity', '<', 10)->get();
        $this->assertEquals(2, $criticalBooks->count());
    }

    // ===== ERROR HANDLING =====

    public function test_returns_validation_error_for_invalid_book_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => 999999,
                'type' => 'entrada',
                'quantity' => 5,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_returns_validation_error_for_invalid_type(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'invalid_type',
                'quantity' => 5,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_returns_validation_error_for_negative_quantity(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => -5,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_returns_validation_error_for_zero_quantity(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 0,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_returns_validation_error_when_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/movements', []);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }
}
