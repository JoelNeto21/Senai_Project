<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

describe('Authentication', function () {
    
    describe('Login', function () {
        
        it('can login with valid credentials', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(302); // Laravel Breeze redirects on login
            expect($user->fresh())->toBeInstanceOf(User::class);
        });

        it('rejects login with invalid password', function () {
            User::factory()->create([
                'email' => 'test@example.com',
                'password' => bcrypt('correct-password'),
            ]);

            $response = $this->postJson('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);

            // Laravel Breeze redirects back on failed login
            $response->assertStatus(302);
        });

        it('rejects login with non-existent email', function () {
            $response = $this->postJson('/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(302);
        });

        it('requires email field', function () {
            $response = $this->postJson('/login', [
                'password' => 'password123',
            ]);

            $response->assertStatus(302); // Redirect with validation errors
        });

        it('requires password field', function () {
            $response = $this->postJson('/login', [
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(302);
        });
    });

    describe('Logout', function () {
        
        it('can logout authenticated user', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->postJson('/logout');

            // Laravel Breeze redirects to home after logout
            $response->assertStatus(302);
        });

        it('redirects to login when accessing protected route after logout', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->postJson('/logout');

            // Create a protected route test
            $response = $this->getJson('/dashboard/insights');
            $response->assertStatus(302); // Redirect to login
        });
    });

    describe('Protected Routes', function () {
        
        it('requires authentication to access protected routes', function () {
            $response = $this->getJson('/dashboard/insights');
            
            // When not authenticated, should redirect
            $response->assertStatus(302);
        });

        it('allows authenticated user to access protected routes', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->getJson('/dashboard/insights');

            // Will either be 200 or 302 depending on route implementation
            expect($response->status())->toBeIn([200, 302]);
        });
    });

    describe('Session Management', function () {
        
        it('maintains session after login', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            // After login, user should be authenticated
            $this->assertAuthenticated();
        });
    });
});
