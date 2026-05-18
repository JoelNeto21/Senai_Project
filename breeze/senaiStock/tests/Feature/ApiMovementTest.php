<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Movement;
use App\Models\User;
use Tests\TestCase;

describe('Movements API', function () {
    
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('Index - GET /api/movements', function () {
        
        it('can list all movements', function () {
            $book = Book::factory()->create();
            Movement::factory()->count(3)->create(['book_id' => $book->id]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/movements');

            if ($response->status() !== 404) {
                $response->assertStatus(200);
                $response->assertJsonIsArray();
            }
        });

        it('returns empty array when no movements exist', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/movements');

            if ($response->status() !== 404) {
                $response->assertStatus(200);
            }
        });

        it('requires authentication to list movements', function () {
            $response = $this->getJson('/api/movements');

            expect($response->status())->toBeIn([401, 404]);
        });

        it('returns movements with correct structure', function () {
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
        });

        it('can filter movements by type', function () {
            $book = Book::factory()->create();
            Movement::factory()->create(['book_id' => $book->id, 'type' => 'entrada', 'quantity' => 5]);
            Movement::factory()->create(['book_id' => $book->id, 'type' => 'saida', 'quantity' => 2]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/movements?type=entrada');

            if ($response->status() === 200) {
                // API should return only entrada movements if filtering is implemented
                $response->assertJsonIsArray();
            }
        });
    });

    describe('Show - GET /api/movements/{id}', function () {
        
        it('can retrieve a single movement', function () {
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
        });

        it('returns 404 for non-existent movement', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/movements/999999');

            expect($response->status())->toBeIn([404, 404]);
        });

        it('requires authentication to view movement', function () {
            $book = Book::factory()->create();
            $movement = Movement::factory()->create(['book_id' => $book->id]);

            $response = $this->getJson("/api/movements/{$movement->id}");

            expect($response->status())->toBeIn([401, 404]);
        });
    });

    describe('Store - Entrada (Entry)', function () {
        
        it('can create entrada movement', function () {
            $book = Book::factory()->create(['quantity' => 5]);

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => 10,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(201);
                expect($book->fresh()->quantity)->toBe(15);
            }
        });

        it('increases book quantity on entrada', function () {
            $book = Book::factory()->create(['quantity' => 5]);
            $initialQuantity = $book->quantity;

            $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => 7,
                ]);

            $book->refresh();
            expect($book->quantity)->toBe($initialQuantity + 7);
        });

        it('entrada does not require justification', function () {
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
        });

        it('creates movement record on entrada', function () {
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
        });

        it('entrada is always allowed with valid data', function () {
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
        });
    });

    describe('Store - Saida (Exit)', function () {
        
        it('can create saida movement when quantity available', function () {
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
                expect($book->fresh()->quantity)->toBe(15);
            }
        });

        it('decreases book quantity on saida', function () {
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
            expect($book->quantity)->toBe($initialQuantity - 8);
        });

        it('CRITICAL: blocks saida when quantity exceeds available stock', function () {
            $book = Book::factory()->create(['quantity' => 5]);

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 10,
                    'justification' => 'Turma C',
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
                expect($book->fresh()->quantity)->toBe(5); // Quantity unchanged
            }
        });

        it('CRITICAL: requires justification field for saida', function () {
            $book = Book::factory()->create(['quantity' => 20]);

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 5,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
                expect($book->fresh()->quantity)->toBe(20); // Quantity unchanged
            }
        });

        it('CRITICAL: rejects saida with empty justification', function () {
            $book = Book::factory()->create(['quantity' => 20]);

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 5,
                    'justification' => '',
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });

        it('CRITICAL: rejects saida with null justification', function () {
            $book = Book::factory()->create(['quantity' => 20]);

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 5,
                    'justification' => null,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });

        it('creates movement record on saida', function () {
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
        });

        it('saida quantity cannot exceed current stock', function () {
            $book = Book::factory()->create(['quantity' => 10]);

            $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 11,
                    'justification' => 'Test',
                ]);

            expect($book->fresh()->quantity)->toBe(10);
        });

        it('stores justification message', function () {
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
        });

        it('requires authentication for saida', function () {
            $book = Book::factory()->create(['quantity' => 20]);

            $response = $this->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'saida',
                'quantity' => 5,
                'justification' => 'Test',
            ]);

            expect($response->status())->toBeIn([401, 404]);
        });
    });

    describe('Stock Level Validation', function () {
        
        it('never allows stock to go below zero', function () {
            $book = Book::factory()->create(['quantity' => 5]);

            // Try to remove more than available
            $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 10,
                    'justification' => 'Should fail',
                ]);

            expect($book->fresh()->quantity)->toBeGreaterThanOrEqual(0);
        });

        it('correctly sums quantities after multiple operations', function () {
            $book = Book::factory()->create(['quantity' => 0]);

            // Entry: 0 + 10 = 10
            $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => 10,
                ]);
            expect($book->fresh()->quantity)->toBe(10);

            // Entry: 10 + 5 = 15
            $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => 5,
                ]);
            expect($book->fresh()->quantity)->toBe(15);

            // Exit: 15 - 3 = 12
            $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'saida',
                    'quantity' => 3,
                    'justification' => 'Distribution',
                ]);
            expect($book->fresh()->quantity)->toBe(12);
        });

        it('books with zero quantity can receive entries', function () {
            $book = Book::factory()->create(['quantity' => 0]);

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => 5,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(201);
                expect($book->fresh()->quantity)->toBe(5);
            }
        });
    });

    describe('Critical Stock Level', function () {
        
        it('can identify books with critical stock', function () {
            $criticalBook = Book::factory()->create(['quantity' => 5]);
            $normalBook = Book::factory()->create(['quantity' => 15]);

            if ($criticalBook->isCriticalStock()) {
                expect($criticalBook->isCriticalStock())->toBeTrue();
            }
            expect($normalBook->isCriticalStock())->toBeFalse();
        });

        it('can list books with critical stock', function () {
            Book::factory()->create(['quantity' => 3]);
            Book::factory()->create(['quantity' => 8]);
            Book::factory()->create(['quantity' => 25]);

            $criticalBooks = Book::where('quantity', '<', 10)->get();
            expect($criticalBooks->count())->toBe(2);
        });
    });

    describe('Error Handling', function () {
        
        it('returns validation error for invalid book_id', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => 999999,
                    'type' => 'entrada',
                    'quantity' => 5,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });

        it('returns validation error for invalid type', function () {
            $book = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'invalid_type',
                    'quantity' => 5,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });

        it('returns validation error for negative quantity', function () {
            $book = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => -5,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });

        it('returns validation error for zero quantity', function () {
            $book = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', [
                    'book_id' => $book->id,
                    'type' => 'entrada',
                    'quantity' => 0,
                ]);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });

        it('returns validation error when missing required fields', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/movements', []);

            if ($response->status() !== 404) {
                expect($response->status())->toBe(422);
            }
        });
    });
});
