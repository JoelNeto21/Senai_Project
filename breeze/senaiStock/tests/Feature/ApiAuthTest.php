<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    public function test_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticated();
    }

    public function test_rejects_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    public function test_rejects_login_with_non_existent_email(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    public function test_requires_email_field(): void
    {
        $response = $this->postJson('/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_requires_password_field(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_logout_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/logout');

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_requires_authentication_to_access_protected_routes(): void
    {
        $response = $this->getJson('/dashboard/insights');

        $response->assertStatus(302);
    }

    public function test_employee_dashboard_requires_employee_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/dashboard/insights');

        $response->assertStatus(302);
    }
}
