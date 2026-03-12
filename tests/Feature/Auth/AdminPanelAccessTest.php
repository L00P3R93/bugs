<?php

use App\Models\User;

test('unverified users cannot access admin panel', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('Tester');

    $response = $this->actingAs($user)->get('/admin');

    $response->assertRedirect(route('verification.notice'));
});

test('verified admin users can access admin panel', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Admin');

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});

test('verified tester users can access admin panel', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Tester');

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});

test('verified non-admin users without roles cannot access admin panel', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('dashboard route redirects to admin panel', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Tester');

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/admin');
});

test('new registration redirects to email verification', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '254712345678',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $response = $this->post('/register', $userData);

    $response->assertRedirect(route('verification.notice'));
});

test('unverified user login redirects to email verification when accessing protected routes', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('Tester');

    // Login should work but redirect when accessing protected routes
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Accessing dashboard should redirect to verification
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('verification.notice'));
});

test('verified user login can access protected routes', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Tester');

    // Login should work
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Accessing dashboard should work
    $response = $this->get('/dashboard');
    $response->assertRedirect('/admin');
});
