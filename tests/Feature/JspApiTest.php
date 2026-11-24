<?php

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('t', 32))]);
    $this->admin = User::factory()
        ->admin()
        ->create(['email' => 'admin@mybook.test', 'password' => Hash::make('AdminDemo123!')]);
    $this->librarian = User::factory()->librarian()->create();
    $this->member = User::factory()->create();
    $this->other = User::factory()->create();
    $this->categoryId = DB::table('categories')->insertGetId([
        'name' => 'Fiksi',
        'description' => 'Novel',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->authorId = DB::table('authors')->insertGetId([
        'name' => 'Penulis',
        'country' => 'Indonesia',
        'biography' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->publisherId = DB::table('publishers')->insertGetId([
        'name' => 'Penerbit',
        'city' => 'Surabaya',
        'email' => 'publisher@test.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->bookId = DB::table('books')->insertGetId([
        'title' => 'Buku Uji',
        'author' => 'Penulis',
        'isbn' => '9781234567890',
        'description' => 'Deskripsi',
        'total_stock' => 1,
        'categories_id' => $this->categoryId,
        'author_id' => $this->authorId,
        'publisher_id' => $this->publisherId,
        'published_year' => 2020,
        'shelf' => 'A-1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->token = $this->postJson('/api/jsp/login', [
        'email' => 'admin@mybook.test',
        'password' => 'AdminDemo123!',
    ])
        ->assertOk()
        ->json('token');
});

function jspHeaders(string $token): array
{
    return ['Authorization' => 'Bearer '.$token];
}
function jspLoan(int $memberId, int $bookId): array
{
    return [
        'member_id' => $memberId,
        'book_id' => $bookId,
        'borrowed_on' => today()->toDateString(),
        'due_on' => today()->addDays(14)->toDateString(),
        'returned_on' => null,
        'notes' => '',
    ];
}

test('JSP API supports all eight CRUD modules', function () {
    $inputs = [
        'categories' => ['name' => 'Baru', 'description' => 'Deskripsi'],
        'authors' => ['name' => 'Penulis Baru', 'country' => 'Indonesia', 'biography' => 'Bio'],
        'publishers' => ['name' => 'Penerbit Baru', 'city' => 'Jakarta', 'email' => 'new@test.com'],
        'books' => [
            'title' => 'Buku Baru',
            'isbn' => '9781234567899',
            'category_id' => $this->categoryId,
            'author_id' => $this->authorId,
            'publisher_id' => $this->publisherId,
            'published_year' => 2021,
            'stock' => 3,
            'shelf' => 'B-1',
            'description' => 'Sinopsis',
        ],
        'members' => [
            'name' => 'Anggota Baru',
            'email' => 'new-member@test.com',
            'password' => 'MemberDemo123!',
            'phone' => '',
            'address' => '',
        ],
        'librarians' => [
            'name' => 'Pustakawan Baru',
            'email' => 'new-librarian@test.com',
            'password' => 'Librarian123!',
            'phone' => '',
            'address' => '',
        ],
        'reservations' => [
            'member_id' => $this->member->id,
            'book_id' => $this->bookId,
            'reserved_on' => today()->toDateString(),
            'status' => 'pending',
            'notes' => '',
        ],
        'loans' => jspLoan($this->member->id, $this->bookId) + [],
    ];
    $inputs['loans']['returned_on'] = today()->toDateString();
    foreach ($inputs as $module => $input) {
        $headers = jspHeaders($this->token);
        $this->postJson('/api/jsp/'.$module, $input, $headers)->assertOk();
        $rows = $this->getJson('/api/jsp/'.$module, $headers)
            ->assertOk()
            ->json();
        $id = $rows[0]['id'];
        $this->getJson('/api/jsp/'.$module.'/'.$id, $headers)->assertOk();
        $this->getJson('/api/jsp/'.$module.'/options', $headers)->assertOk();
        $field = isset($input['name']) ? 'name' : ($module === 'books' ? 'title' : 'notes');
        $input[$field] = 'Diperbarui';
        if (in_array($module, ['members', 'librarians'])) {
            $input['password'] = '';
        }
        $this->putJson('/api/jsp/'.$module.'/'.$id, $input, $headers)->assertOk();
        $this->getJson('/api/jsp/'.$module.'/'.$id, $headers)
            ->assertOk()
            ->assertJsonPath($field, 'Diperbarui');
        $this->deleteJson('/api/jsp/'.$module.'/'.$id, [], $headers)->assertOk();
        $this->getJson('/api/jsp/'.$module.'/'.$id, $headers)->assertNotFound();
    }
});

test('JSP API preserves inventory and atomically picks up reservations', function () {
    $h = jspHeaders($this->token);
    $this->postJson(
        '/api/jsp/reservations',
        [
            'member_id' => $this->member->id,
            'book_id' => $this->bookId,
            'reserved_on' => today()->toDateString(),
            'status' => 'ready',
        ],
        $h,
    )->assertOk();
    $rid = DB::table('reservations')->max('id');
    $this->postJson('/api/jsp/loans', jspLoan($this->other->id, $this->bookId), $h)->assertUnprocessable();
    $wrong = jspLoan($this->other->id, $this->bookId) + ['reservation_id' => $rid];
    $this->postJson('/api/jsp/loans', $wrong, $h)->assertUnprocessable();
    expect(DB::table('reservations')->where('id', $rid)->value('status'))->toBe('ready_for_pickup');
    $loan = jspLoan($this->member->id, $this->bookId) + ['reservation_id' => $rid];
    $this->postJson('/api/jsp/loans', $loan, $h)->assertOk();
    expect(DB::table('reservations')->where('id', $rid)->value('status'))->toBe('completed');
    $id = DB::table('loans')->max('id');
    $this->deleteJson('/api/jsp/loans/'.$id, [], $h)->assertUnprocessable();
    $loan['returned_on'] = today()->toDateString();
    $this->putJson('/api/jsp/loans/'.$id, $loan, $h)->assertOk();
    $this->getJson('/api/jsp/books/'.$this->bookId, $h)
        ->assertOk()
        ->assertJsonPath('available', 1);
});

test('JSP API enforces roles ownership and active accounts', function () {
    $memberToken = Crypt::encryptString(
        json_encode(['id' => $this->member->id, 'purpose' => 'jsp-api', 'expires' => time() + 600]),
    );
    $h = jspHeaders($memberToken);
    $this->postJson(
        '/api/jsp/reservations',
        [
            'member_id' => $this->other->id,
            'book_id' => $this->bookId,
            'reserved_on' => today()->toDateString(),
            'status' => 'ready',
        ],
        $h,
    )->assertOk();
    $rid = DB::table('reservations')->max('id');
    expect(DB::table('reservations')->where('id', $rid)->value('users_id'))->toBe($this->member->id);
    expect(DB::table('reservations')->where('id', $rid)->value('status'))->toBe('pending');
    $otherToken = Crypt::encryptString(
        json_encode(['id' => $this->other->id, 'purpose' => 'jsp-api', 'expires' => time() + 600]),
    );
    $this->getJson('/api/jsp/reservations/'.$rid, jspHeaders($otherToken))->assertNotFound();
    $this->getJson('/api/jsp/members', $h)->assertForbidden();
    $this->postJson('/api/jsp/loans', jspLoan($this->member->id, $this->bookId), $h)->assertForbidden();
    $this->getJson('/api/jsp/books')->assertUnauthorized();
    $this->member->update(['is_active' => false]);
    $this->getJson('/api/jsp/me', $h)->assertUnauthorized();
});

test('JSP API rejects invalid dates and referenced deletes without leaking passwords', function () {
    $h = jspHeaders($this->token);
    $this->deleteJson('/api/jsp/categories/'.$this->categoryId, [], $h)->assertUnprocessable();
    $loan = jspLoan($this->member->id, $this->bookId);
    $loan['due_on'] = today()->subDay()->toDateString();
    $this->postJson('/api/jsp/loans', $loan, $h)->assertUnprocessable();
    $this->getJson('/api/jsp/members', $h)->assertOk()->assertJsonMissingPath('0.password');
    $this->getJson('/api/jsp/dashboard', $h)->assertOk()->assertJsonPath('books', 1);
    $this->postJson('/api/jsp/login', [
        'email' => 'admin@mybook.test',
        'password' => 'wrong',
    ])->assertUnauthorized();
});

function jspMemberToken(User $user): string
{
    return Crypt::encryptString(
        json_encode(['id' => $user->id, 'purpose' => 'jsp-api', 'expires' => time() + 600]),
    );
}

test('loans returns and reservations use Jakarta dates when UTC is still the previous day', function () {
    expect(config('app.timezone'))->toBe('Asia/Jakarta');
    Date::setTestNow(CarbonImmutable::parse('2026-11-24 18:30:00', 'UTC'));

    try {
        expect(today()->toDateString())->toBe('2026-11-25');
        expect(now('UTC')->toDateString())->toBe('2026-11-24');
        $headers = jspHeaders($this->token);
        $reservation = [
            'member_id' => $this->member->id,
            'book_id' => $this->bookId,
            'reserved_on' => '2026-11-25',
            'status' => 'pending',
        ];
        $this->postJson('/api/jsp/reservations', $reservation, $headers)->assertOk();

        $loan = jspLoan($this->member->id, $this->bookId);
        $loan['borrowed_on'] = '2026-11-25';
        $loan['due_on'] = '2026-12-09';
        $this->postJson('/api/jsp/loans', $loan, $headers)->assertOk();
        $id = DB::table('loans')->max('id');

        $loan['returned_on'] = '2026-11-25';
        $this->putJson('/api/jsp/loans/'.$id, $loan, $headers)->assertOk();
        $loan['returned_on'] = '2026-11-26';
        $this->putJson('/api/jsp/loans/'.$id, $loan, $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('returned_on');

        $loan['returned_on'] = null;
        $loan['borrowed_on'] = '2026-11-26';
        $this->postJson('/api/jsp/loans', $loan, $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('borrowed_on');
        $reservation['reserved_on'] = '2026-11-26';
        $this->postJson('/api/jsp/reservations', $reservation, $headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reserved_on');
    } finally {
        Date::setTestNow();
    }
});

test('favorites are private idempotent and removable only for the signed in member', function () {
    $h = jspHeaders(jspMemberToken($this->member));
    $other = jspHeaders(jspMemberToken($this->other));
    $url = '/api/jsp/books/'.$this->bookId.'/favorite';
    $this->getJson('/api/jsp/favorites', $h)->assertOk()->assertExactJson([]);
    $this->postJson($url, ['users_id' => $this->other->id], $h)->assertOk();
    $this->postJson($url, [], $h)->assertOk();
    expect(DB::table('favorites')->count())->toBe(1);
    $this->getJson('/api/jsp/favorites', $h)
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $this->bookId);
    $this->getJson('/api/jsp/favorites', $other)->assertOk()->assertExactJson([]);
    $this->deleteJson($url, ['users_id' => $this->member->id], $other)->assertOk();
    expect(DB::table('favorites')->count())->toBe(1);
    $this->getJson('/api/jsp/books/'.$this->bookId.'/community', $h)
        ->assertOk()
        ->assertJsonPath('favorite', true);
    $this->deleteJson($url, [], $h)->assertOk();
    $this->getJson('/api/jsp/favorites', $h)->assertOk()->assertExactJson([]);
    $this->postJson($url, [], jspHeaders($this->token))->assertForbidden();
    $this->postJson('/api/jsp/books/99999/favorite', [], $h)->assertNotFound();
});

test('reviews have full CRUD validated ratings ownership and accurate averages', function () {
    $h = jspHeaders(jspMemberToken($this->member));
    $other = jspHeaders(jspMemberToken($this->other));
    $url = '/api/jsp/books/'.$this->bookId.'/review';
    $community = '/api/jsp/books/'.$this->bookId.'/community';
    $this->getJson($community, $h)->assertOk()->assertJsonPath('average', null)->assertJsonPath('count', 0);
    foreach ([0, 6, 'bad'] as $rating) {
        $this->postJson($url, ['rating' => $rating, 'comment' => 'Ulasan'], $h)->assertUnprocessable();
    }
    $this->postJson($url, ['rating' => 5, 'comment' => str_repeat('a', 1001)], $h)->assertUnprocessable();
    $this->postJson($url, ['rating' => 5, 'comment' => '   '], $h)->assertUnprocessable();
    $this->postJson(
        $url,
        ['rating' => 5, 'comment' => '<script>alert(1)</script>', 'users_id' => $this->other->id],
        $h,
    )->assertOk();
    $this->postJson($url, ['rating' => 3, 'comment' => 'Duplikat'], $h)->assertUnprocessable();
    $this->getJson($community, $h)
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('own_review.rating', 5)
        ->assertJsonMissingPath('reviews.0.email');
    $this->putJson($url, ['rating' => 1, 'comment' => 'Bukan pemilik'], $other)->assertNotFound();
    $this->deleteJson($url, [], $other)->assertNotFound();
    $this->postJson($url, ['rating' => 3, 'comment' => 'Cukup baik'], $other)->assertOk();
    $this->getJson($community, $h)->assertOk()->assertJsonPath('count', 2)->assertJsonPath('average', 4);
    $this->putJson($url, ['rating' => 1, 'comment' => 'Diperbarui'], $h)->assertOk();
    $this->getJson($community, $h)
        ->assertOk()
        ->assertJsonPath('own_review.comment', 'Diperbarui')
        ->assertJsonPath('average', 2);
    $this->deleteJson($url, [], $h)->assertOk();
    $this->getJson($community, $h)
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('own_review', null)
        ->assertJsonPath('average', 3);
    $this->postJson($url, ['rating' => 5, 'comment' => 'Admin'], jspHeaders($this->token))->assertForbidden();
});

test('review lists are paginated and deleting a book cascades community records', function () {
    $h = jspHeaders(jspMemberToken($this->member));
    $rows = [];
    for ($i = 0; $i < 12; $i++) {
        $user = User::factory()->create();
        $rows[] = [
            'users_id' => $user->id,
            'books_id' => $this->bookId,
            'rating' => 4,
            'comment' => 'Ulasan '.$i,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    DB::table('reviews')->insert($rows);
    $this->getJson('/api/jsp/books/'.$this->bookId.'/community', $h)
        ->assertOk()
        ->assertJsonCount(10, 'reviews')
        ->assertJsonPath('count', 12)
        ->assertJsonPath('has_next', true);
    $this->getJson('/api/jsp/books/'.$this->bookId.'/community?page=2', $h)
        ->assertOk()
        ->assertJsonCount(2, 'reviews')
        ->assertJsonPath('has_next', false);
    $this->postJson('/api/jsp/books/'.$this->bookId.'/favorite', [], $h)->assertOk();
    $this->deleteJson('/api/jsp/books/'.$this->bookId, [], jspHeaders($this->token))->assertOk();
    expect(DB::table('reviews')->count())->toBe(0);
    expect(DB::table('favorites')->count())->toBe(0);
});
