<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Movement;
use App\Models\User;
use Tests\TestCase;

class BookMovementStructureTest extends TestCase
{
    /**
     * Test Book model structure and relationships.
     */
    public function test_book_model_has_correct_fillable_properties(): void
    {
        $book = new Book();
        $fillable = $book->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('isbn', $fillable);
        $this->assertContains('subject', $fillable);
        $this->assertContains('quantity', $fillable);
    }

    /**
     * Test Movement model structure and relationships.
     */
    public function test_movement_model_has_correct_fillable_properties(): void
    {
        $movement = new Movement();
        $fillable = $movement->getFillable();

        $this->assertContains('type', $fillable);
        $this->assertContains('book_id', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('funcionario_id', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('justification', $fillable);
    }

    /**
     * Test Book relationships.
     */
    public function test_book_has_many_movements(): void
    {
        $this->assertTrue(method_exists(Book::class, 'movements'));
    }

    /**
     * Test Movement relationships.
     */
    public function test_movement_belongs_to_book(): void
    {
        $this->assertTrue(method_exists(Movement::class, 'book'));
    }

    public function test_movement_belongs_to_user(): void
    {
        $this->assertTrue(method_exists(Movement::class, 'user'));
    }

    public function test_movement_belongs_to_funcionario(): void
    {
        $this->assertTrue(method_exists(Movement::class, 'funcionario'));
    }

    /**
     * Test Book helper methods.
     */
    public function test_book_critical_stock_check(): void
    {
        $book = new Book(['quantity' => 8]);
        $this->assertTrue($book->isCriticalStock());

        $book = new Book(['quantity' => 15]);
        $this->assertFalse($book->isCriticalStock());
    }

    public function test_book_out_of_stock_check(): void
    {
        $book = new Book(['quantity' => 0]);
        $this->assertTrue($book->isOutOfStock());

        $book = new Book(['quantity' => 5]);
        $this->assertFalse($book->isOutOfStock());
    }

    /**
     * Test Movement helper methods.
     */
    public function test_movement_is_entry(): void
    {
        $movement = new Movement(['type' => 'entrada']);
        $this->assertTrue($movement->isEntry());

        $movement = new Movement(['type' => 'saida']);
        $this->assertFalse($movement->isEntry());
    }

    public function test_movement_is_exit(): void
    {
        $movement = new Movement(['type' => 'saida']);
        $this->assertTrue($movement->isExit());

        $movement = new Movement(['type' => 'entrada']);
        $this->assertFalse($movement->isExit());
    }

    /**
     * Test User relationships.
     */
    public function test_user_has_many_movements(): void
    {
        $this->assertTrue(method_exists(User::class, 'movements'));
    }
}
