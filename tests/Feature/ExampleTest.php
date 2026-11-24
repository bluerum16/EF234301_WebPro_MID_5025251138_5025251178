<?php

use App\Models\User;

test('guests are redirected from home to login', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});

test('authenticated users are redirected from home to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

test('hybrid browser routes redirect to JSP before rendering Blade assets', function () {
    config(['app.jsp_url' => 'http://localhost:8080']);

    $this->get('/')->assertRedirect('http://localhost:8080/app/dashboard');
    $this->get('/login')->assertRedirect('http://localhost:8080/app/dashboard');
    $this->get('/books')->assertRedirect('http://localhost:8080/app/dashboard');
});

test('hybrid redirect leaves JSON API responses unchanged', function () {
    config(['app.jsp_url' => 'http://localhost:8080']);

    $this->getJson('/api/jsp/me')->assertUnauthorized()->assertJsonStructure(['message']);
});
