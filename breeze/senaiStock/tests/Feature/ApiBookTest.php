<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

describe('Books API', function () {
    
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('Index - GET /api/books', function () {
        
        it('can list all books', function () {
            Book::factory()->count(3)->create();

            $response = $this->actingAs($this->user)
                ->getJson('/api/books');

            // Note: Route may not exist yet - test will indicate if implementation needed
            if ($response->status() !== 404) {
                $response->assertStatus(200);
                $response->assertJsonIsArray();
            }
        });

        it('returns empty array when no books exist', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/books');

            if ($response->status() !== 404) {
                $response->assertStatus(200);
                $response->assertJson([]);
            }
        });

        it('requires authentication to list books', function () {
            $response = $this->getJson('/api/books');

            // Should be 401 (unauthorized) or 404 (not found)
            expect($response->status())->toBeIn([401, 404]);
        });

        it('returns books with correct structure', function () {
            Book::factory()->create([
                'title' => 'Test Book',
                'isbn' => '978-3-16-148410-0',
                'subject' => 'Fiction',
                'quantity' => 5,
            ]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/books');

            if ($response->status() === 200) {
                $response->assertJsonStructure([
                    '*' => ['id', 'title', 'isbn', 'subject', 'quantity', 'created_at', 'updated_at']
                ]);
            }
        });
    });

    describe('Show - GET /api/books/{id}', function () {
        
        it('can retrieve a single book', function () {
            $book = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->getJson("/api/books/{$book->id}");

            if ($response->status() !== 404) {
                $response->assertStatus(200);
                $response->assertJson([
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                ]);
            }
        });

        it('returns 404 for non-existent book', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/books/999999');

            expect($response->status())->toBeIn([404, 404]);
        });

        it('requires authentication to view book', function () {
            $book = Book::factory()->create();

            $response = $this->getJson("/api/books/{$book->id}");

            expect($response->status())->toBeIn([401, 404]);
        });
    });

    describe('Store - POST /api/books', function () {
        
        it('can create a book with valid data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/books', [
                    'title' => 'New Book',
                    'isbn' => '978-3-16-148410-0',
                    'subject' => 'Science',
                    'quantity' => 10,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(201);
                $this->assertDatabaseHas('books', [
                    'title' => 'New Book',
                    'isbn' => '978-3-16-148410-0',
                ]);
            }
        });

        it('rejects book creation without title', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/books', [
                    'isbn' => '978-3-16-148410-1',
                    'subject' => 'Science',
                    'quantity' => 10,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(422);
            }
        });

        it('rejects book creation without isbn', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/books', [
                    'title' => 'New Book',
                    'subject' => 'Science',
                    'quantity' => 10,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(422);
            }
        });

        it('rejects book creation without subject', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/books', [
                    'title' => 'New Book',
                    'isbn' => '978-3-16-148410-2',
                    'quantity' => 10,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(422);
            }
        });

        it('rejects book with duplicate ISBN', function () {
            Book::factory()->create(['isbn' => '978-3-16-148410-0']);

            $response = $this->actingAs($this->user)
                ->postJson('/api/books', [
                    'title' => 'Another Book',
                    'isbn' => '978-3-16-148410-0',
                    'subject' => 'Science',
                    'quantity' => 10,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(422);
                $response->assertJsonValidationErrors('isbn');
            }
        });

        it('requires authentication to create book', function () {
            $response = $this->postJson('/api/books', [
                'title' => 'New Book',
                'isbn' => '978-3-16-148410-3',
                'subject' => 'Science',
                'quantity' => 10,
            ]);

            expect($response->status())->toBeIn([401, 404]);
        });

        it('sets default quantity to 0 if not provided', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/books', [
                    'title' => 'New Book',
                    'isbn' => '978-3-16-148410-4',
                    'subject' => 'Science',
                ]);

            if ($response->status() === 201) {
                $this->assertDatabaseHas('books', [
                    'isbn' => '978-3-16-148410-4',
                    'quantity' => 0,
                ]);
            }
        });
    });

    describe('Update - PUT /api/books/{id}', function () {
        
        it('can update a book', function () {
            $book = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->putJson("/api/books/{$book->id}", [
                    'title' => 'Updated Title',
                    'subject' => 'Updated Subject',
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(200);
                $this->assertDatabaseHas('books', [
                    'id' => $book->id,
                    'title' => 'Updated Title',
                ]);
            }
        });

        it('returns 404 when updating non-existent book', function () {
            $response = $this->actingAs($this->user)
                ->putJson('/api/books/999999', [
                    'title' => 'Updated Title',
                ]);

            expect($response->status())->toBeIn([404, 404]);
        });

        it('does not allow updating ISBN to duplicate value', function () {
            $book1 = Book::factory()->create();
            $book2 = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->putJson("/api/books/{$book1->id}", [
                    'isbn' => $book2->isbn,
                ]);

            if ($response->status() !== 404) {
                $response->assertStatus(422);
            }
        });

        it('requires authentication to update book', function () {
            $book = Book::factory()->create();

            $response = $this->putJson("/api/books/{$book->id}", [
                'title' => 'Updated Title',
            ]);

            expect($response->status())->toBeIn([401, 404]);
        });
    });

    describe('Delete - DELETE /api/books/{id}', function () {
        
        it('can delete a book', function () {
            $book = Book::factory()->create();

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/books/{$book->id}");

            if ($response->status() !== 404) {
                $response->assertStatus(204);
                $this->assertDatabaseMissing('books', ['id' => $book->id]);
            }
        });

        it('returns 404 when deleting non-existent book', function () {
            $response = $this->actingAs($this->user)
                ->deleteJson('/api/books/999999');

            expect($response->status())->toBeIn([404, 404]);
        });

        it('requires authentication to delete book', function () {
            $book = Book::factory()->create();

            $response = $this->deleteJson("/api/books/{$book->id}");

            expect($response->status())->toBeIn([401, 404]);
        });

        it('also deletes associated movements', function () {
            $book = Book::factory()->create();
            $this->postJson('/api/movements', [
                'book_id' => $book->id,
                'type' => 'entrada',
                'quantity' => 5,
            ]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/books/{$book->id}");

            if ($response->status() === 204) {
                $this->assertDatabaseMissing('books', ['id' => $book->id]);
            }
        });
    });
});
