<?php

use App\Models\User;

test('members can not open admin or librarian pages', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('librarian.books.index'))->assertForbidden();
});

test('librarians can not open admin pages', function () {
    $this->actingAs(User::factory()->librarian()->create());

    $this->get(route('librarian.books.index'))->assertOk();
    $this->get(route('admin.categories.index'))->assertForbidden();
});

test('inactive users can not log in', function () {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
