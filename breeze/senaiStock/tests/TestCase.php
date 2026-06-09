<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cargo;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup that runs before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Create some default cargos for testing
        Cargo::factory()->createMany(3);
    }

    protected function assertIn(mixed $needle, array $haystack, string $message = ''): void
    {
        $this->assertContains($needle, $haystack, $message);
    }

    protected function assertIsNotNull(mixed $actual, string $message = ''): void
    {
        $this->assertNotNull($actual, $message);
    }

    protected function withEmployeeSession(string $cargo = 'Coordenador', ?string $name = null): static
    {
        $name ??= match ($cargo) {
            'Professor' => 'Prof. Carlos Mendes',
            default => 'Coordenador Senai',
        };

        return $this->withSession([
            'employee' => [
                'id' => 1,
                'name' => $name,
                'cargo' => $cargo,
            ],
        ]);
    }
}
