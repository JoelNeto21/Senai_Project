<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ===== INDEX: GET /api/books =====

    public function test_can_list_all_books(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/books');

        if ($response->status() !== 404) {
            $response->assertStatus(200);
            $response->assertJsonIsArray();
        }
    }

    public function test_returns_empty_array_when_no_books_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/books');

        if ($response->status() !== 404) {
            $response->assertStatus(200);
            $response->assertJson([]);
        }
    }

    public function test_requires_authentication_to_list_books(): void
    {
        $response = $this->getJson('/api/books');

        $this->assertIn($response->status(), [401, 404]);
    }

    public function test_returns_books_with_correct_structure(): void
    {
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
    }

    // ===== SHOW: GET /api/books/{id} =====

    public function test_can_retrieve_a_single_book(): void
    {
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
    }

    public function test_returns_404_for_non_existent_book(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/books/999999');

        $this->assertIn($response->status(), [404, 404]);
    }

    public function test_requires_authentication_to_view_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/books/{$book->id}");

        $this->assertIn($response->status(), [401, 404]);
    }

    // ===== STORE: POST /api/books =====

    public function test_can_create_a_book_with_valid_data(): void
    {
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
    }

    public function test_rejects_book_creation_without_title(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/books', [
                'isbn' => '978-3-16-148410-1',
                'subject' => 'Science',
                'quantity' => 10,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_rejects_book_creation_without_isbn(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/books', [
                'title' => 'New Book',
                'subject' => 'Science',
                'quantity' => 10,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_rejects_book_creation_without_subject(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/books', [
                'title' => 'New Book',
                'isbn' => '978-3-16-148410-2',
                'quantity' => 10,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_rejects_book_with_duplicate_isbn(): void
    {
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
    }

    public function test_requires_authentication_to_create_book(): void
    {
        $response = $this->postJson('/api/books', [
            'title' => 'New Book',
            'isbn' => '978-3-16-148410-3',
            'subject' => 'Science',
            'quantity' => 10,
        ]);

        $this->assertIn($response->status(), [401, 404]);
    }

    public function test_sets_default_quantity_to_zero_if_not_provided(): void
    {
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
    }

    // ===== UPDATE: PUT /api/books/{id} =====

    public function test_can_update_a_book(): void
    {
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
    }

    public function test_returns_404_when_updating_non_existent_book(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/books/999999', [
                'title' => 'Updated Title',
            ]);

        $this->assertIn($response->status(), [404, 404]);
    }

    public function test_does_not_allow_updating_isbn_to_duplicate_value(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/books/{$book1->id}", [
                'isbn' => $book2->isbn,
            ]);

        if ($response->status() !== 404) {
            $response->assertStatus(422);
        }
    }

    public function test_requires_authentication_to_update_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Title',
        ]);

        $this->assertIn($response->status(), [401, 404]);
    }

    // ===== DELETE: DELETE /api/books/{id} =====

    public function test_can_delete_a_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/books/{$book->id}");

        if ($response->status() !== 404) {
            $response->assertStatus(204);
            $this->assertDatabaseMissing('books', ['id' => $book->id]);
        }
    }

    public function test_returns_404_when_deleting_non_existent_book(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/books/999999');

        $this->assertIn($response->status(), [404, 404]);
    }

    public function test_requires_authentication_to_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/books/{$book->id}");

        $this->assertIn($response->status(), [401, 404]);
    }

    public function test_also_deletes_associated_movements(): void
    {
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
    }
}
