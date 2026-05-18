<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['entrada', 'saida']);

        return [
            'type' => $type,
            'book_id' => Book::factory(),
            'user_id' => User::factory(),
            'quantity' => $this->faker->numberBetween(1, 20),
            'justification' => $type === 'saida'
                ? $this->faker->sentence()
                : null,
        ];
    }

    /**
     * State for an entrada (entry) movement.
     */
    public function entrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'entrada',
            'justification' => null,
        ]);
    }

    /**
     * State for a saida (exit) movement.
     */
    public function saida(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'saida',
            'justification' => $this->faker->sentence(),
        ]);
    }

    /**
     * State for a saida with a specific book.
     */
    public function forBook(Book $book): static
    {
        return $this->state(fn (array $attributes) => [
            'book_id' => $book->id,
        ]);
    }

    /**
     * State for a movement by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * State for a large quantity movement.
     */
    public function largeQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(50, 100),
        ]);
    }

    /**
     * State for a small quantity movement.
     */
    public function smallQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 5),
        ]);
    }
}
