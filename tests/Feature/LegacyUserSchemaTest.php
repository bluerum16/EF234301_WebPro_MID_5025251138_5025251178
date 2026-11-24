<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function legacyUserRoleMigration(): object
{
    return require database_path('migrations/2026_11_25_000001_complete_legacy_user_roles.php');
}

test('legacy user role repair adds missing columns without losing records', function () {
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['role', 'is_active']);
    });
    $id = DB::table('users')->insertGetId([
        'name' => 'Legacy Member',
        'email' => 'legacy@example.test',
        'password' => 'test-placeholder',
    ]);

    legacyUserRoleMigration()->up();
    legacyUserRoleMigration()->up();

    $user = DB::table('users')->where('id', $id)->first(['name', 'role', 'is_active']);
    expect($user->name)->toBe('Legacy Member');
    expect($user->role)->toBe('member');
    expect((bool) $user->is_active)->toBeTrue();
});

test('user role repair preserves existing administrator roles and inactive accounts', function () {
    $id = DB::table('users')->insertGetId([
        'name' => 'Existing Admin',
        'email' => 'admin@example.test',
        'password' => 'test-placeholder',
        'role' => 'admin',
        'is_active' => false,
    ]);

    legacyUserRoleMigration()->up();

    $user = DB::table('users')->where('id', $id)->first(['role', 'is_active']);
    expect($user->role)->toBe('admin');
    expect((bool) $user->is_active)->toBeFalse();
});
