<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admins are sent to the admin dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.dashboard'))->assertOk();
});

test('librarians are sent to the loan list', function () {
    $this->actingAs(User::factory()->librarian()->create());

    $this->get(route('dashboard'))->assertRedirect(route('librarian.loans.index'));
});

test('members are sent to the book catalog', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('dashboard'))->assertRedirect(route('member.books.index'));
    $this->get(route('member.books.index'))->assertOk();
});
