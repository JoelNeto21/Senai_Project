<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'isbn' => $this->faker->unique()->isbn13(),
            'subject' => $this->faker->randomElement([
                'Fiction',
                'Science',
                'History',
                'Technology',
                'Mathematics',
                'Literature',
                'Philosophy',
                'Art',
                'Music',
                'Education',
            ]),
            'description' => $this->faker->sentence(12),
            'image_path' => null,
            'quantity' => $this->faker->numberBetween(0, 50),
            'minimum_stock' => 10,
            'location' => 'Prateleira ' . $this->faker->bothify('??-##'),
            'status' => 'ativo',
        ];
    }

    /**
     * State for a book with critical stock (< 10).
     */
    public function criticalStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 9),
        ]);
    }

    /**
     * State for a book that is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    /**
     * State for a book with normal stock levels.
     */
    public function normalStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(20, 50),
        ]);
    }

    /**
     * State for a book with high stock.
     */
    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(50, 100),
        ]);
    }
}
