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
}
