<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function registrationPayload(array $changes = []): array
{
    return array_replace([
        'name' => 'New Member',
        'email' => 'new.member@example.com',
        'password' => 'MemberPass123!',
        'password_confirmation' => 'MemberPass123!',
    ], $changes);
}

it('registers an active member and issues a working login token without granting staff access', function () {
    $response = $this->postJson('/api/jsp/register', registrationPayload([
        'email' => 'NEW.MEMBER@example.com',
        'role' => 'admin',
        'is_active' => false,
    ]))->assertCreated()->assertJsonPath('user.role', 'member');
    $user = User::query()->where('email', 'new.member@example.com')->firstOrFail();
    expect($user->role)->toBe(Role::Member)
        ->and($user->is_active)->toBeTrue()
        ->and(Hash::check('MemberPass123!', $user->password))->toBeTrue();
    $headers = ['Authorization' => 'Bearer '.$response->json('token')];
    $this->getJson('/api/jsp/me', $headers)->assertOk()->assertJsonPath('id', $user->id);
    $this->getJson('/api/jsp/members', $headers)->assertForbidden();
    $this->postJson('/api/jsp/login', [
        'email' => $user->email, 'password' => 'MemberPass123!',
    ])->assertOk();
});

it('rejects duplicate emails regardless of letter case', function () {
    User::factory()->create(['email' => 'new.member@example.com']);
    $this->postJson('/api/jsp/register', registrationPayload([
        'email' => 'NEW.MEMBER@example.com',
    ]))->assertUnprocessable()->assertJsonValidationErrors('email');
    expect(User::query()->count())->toBe(1);
});

it('rejects invalid registration details without creating an account', function (array $changes, string $field) {
    $this->postJson('/api/jsp/register', registrationPayload($changes))
        ->assertUnprocessable()->assertJsonValidationErrors($field);
    expect(User::query()->count())->toBe(0);
})->with([
    'blank name' => [['name' => '   '], 'name'],
    'invalid email' => [['email' => 'invalid'], 'email'],
    'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
    'confirmation mismatch' => [['password_confirmation' => 'OtherPass123!'], 'password'],
]);
