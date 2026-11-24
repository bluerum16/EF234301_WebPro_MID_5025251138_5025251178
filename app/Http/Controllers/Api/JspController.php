<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JspController extends Controller
{
    private const TABLES = [
        'books' => 'books',
        'categories' => 'categories',
        'authors' => 'authors',
        'publishers' => 'publishers',
        'members' => 'users',
        'librarians' => 'users',
        'reservations' => 'reservations',
        'loans' => 'loans',
    ];

    public function login(Request $request)
    {
        $v = $request->validate([
            'email' => 'required|email|max:180',
            'password' => 'required|string|max:160',
        ]);
        $user = User::query()->where('email', strtolower(trim($v['email'])))->first();
        abort_unless(
            $user && Hash::check($v['password'], $user->password) && $user->is_active,
            401,
            'The email address or password is incorrect.',
        );

        return response()->json([
            'token' => Crypt::encryptString(
                json_encode(['id' => $user->id, 'purpose' => 'jsp-api', 'expires' => time() + 7200]),
            ),
            'user' => $this->identity($user),
        ]);
    }

    private function identity(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role->value];
    }

    public function me(Request $request)
    {
        return response()->json($this->identity($request->user()));
    }

    private function authorizeModule(Request $r, string $module, bool $write = false): void
    {
        abort_unless(isset(self::TABLES[$module]), 404);
        $role = $r->user()->role;
        abort_if(in_array($module, ['members', 'librarians']) && $role !== Role::Admin, 403);
        if ($role === Role::Member) {
            abort_unless(in_array($module, ['books', 'reservations', 'loans']), 403);
            abort_if($write && $module !== 'reservations', 403);
        }
    }

    private function query(Request $r, string $module): Builder
    {
        $q = DB::table(self::TABLES[$module].' as t')->select('t.*');
        if (in_array($module, ['members', 'librarians'])) {
            // Never select password, recovery codes, or other account secrets.
            $q->select('t.id', 't.name', 't.email', 't.phone', 't.address', 't.role')->where(
                't.role',
                $module === 'members' ? 'member' : 'librarian',
            );
        } elseif ($module === 'books') {
            $q->leftJoin('categories as c', 'c.id', '=', 't.categories_id')
                ->leftJoin('authors as a', 'a.id', '=', 't.author_id')
                ->leftJoin('publishers as p', 'p.id', '=', 't.publisher_id')
                ->addSelect('c.name as category', 'a.name as author_name', 'p.name as publisher');
        } elseif (in_array($module, ['reservations', 'loans'])) {
            $q->join('users as u', 'u.id', '=', 't.users_id')
                ->join('books as b', 'b.id', '=', 't.books_id')
                ->addSelect('u.name as member', 'b.title as book');
            if ($r->user()->role === Role::Member) {
                $q->where('t.users_id', $r->user()->id);
            }
        }

        return $q;
    }

    private function normalize(string $module, object $data): array
    {
        $v = (array) $data;
        if ($module === 'books') {
            $v['category_id'] = $v['categories_id'];
            $v['stock'] = $v['total_stock'];
            $v['author'] = $v['author_name'] ?: $v['author'];
            $v['available'] =
                $v['total_stock'] -
                DB::table('loans')->where('books_id', $v['id'])->where('status', 'borrowed')->count() -
                DB::table('reservations')
                    ->where('books_id', $v['id'])
                    ->where('status', 'ready_for_pickup')
                    ->count();
        } elseif (in_array($module, ['reservations', 'loans'])) {
            $v['member_id'] = $v['users_id'];
            $v['book_id'] = $v['books_id'];
            if ($module === 'reservations') {
                $v['reserved_on'] = substr($v['reserved_on'] ?: $v['created_at'], 0, 10);
                $v['status'] = match ($v['status']) {
                    'ready_for_pickup' => 'ready',
                    'completed' => 'picked_up',
                    'rejected', 'expired' => 'cancelled',
                    default => $v['status'],
                };
            } else {
                $v['reservation_id'] = $v['reservations_id'];
                $v['borrowed_on'] = substr($v['borrow_date'], 0, 10);
                $v['due_on'] = substr($v['due_date'], 0, 10);
                $v['returned_on'] = $v['return_date'] ? substr($v['return_date'], 0, 10) : null;
                unset($v['status']); // JSP derives overdue from due_on and returned_on.
            }
        }

        return $v;
    }

    public function index(Request $request, string $module)
    {
        $this->authorizeModule($request, $module);
        $page = max(1, min(100000, (int) $request->query('page', 1)));
        $q = $this->query($request, $module);
        $search = mb_strtolower(mb_substr((string) $request->query('q', ''), 0, 200));
        if ($search !== '') {
            $columns = match ($module) {
                'books' => ['t.title', 't.isbn', 't.author'],
                'reservations', 'loans' => ['u.name', 'b.title'],
                'members', 'librarians' => ['t.name', 't.email'],
                default => ['t.name'],
            };
            $q->where(function (Builder $query) use ($columns, $search) {
                foreach ($columns as $column) {
                    $query->orWhereRaw('LOWER('.$column.') LIKE ?', ['%'.$search.'%']);
                }
            });
        }

        return response()->json(
            $q
                ->orderByDesc('t.id')
                ->offset(($page - 1) * 12)
                ->limit(13)
                ->get()
                ->map(fn ($row) => $this->normalize($module, $row))
                ->all(),
        );
    }

    public function show(Request $request, string $module, int $id)
    {
        $this->authorizeModule($request, $module);
        $row = $this->query($request, $module)->where('t.id', $id)->first();
        abort_unless($row, 404, 'Record not found.');

        return response()->json($this->normalize($module, $row));
    }

    public function favorites(Request $request)
    {
        abort_unless($request->user()->role === Role::Member, 403);
        $page = max(1, min(100000, (int) $request->query('page', 1)));
        $q = $this->query($request, 'books')
            ->join('favorites as f', 'f.books_id', '=', 't.id')
            ->where('f.users_id', $request->user()->id);

        return response()->json(
            $q
                ->orderByDesc('f.id')
                ->offset(($page - 1) * 12)
                ->limit(13)
                ->get()
                ->map(fn ($row) => $this->normalize('books', $row))
                ->all(),
        );
    }

    public function options(Request $request, string $module)
    {
        $this->authorizeModule($request, $module);
        $out = [];
        if ($module === 'books') {
            foreach (
                ['categories' => 'category_id', 'authors' => 'author_id', 'publishers' => 'publisher_id'] as $table => $field
            ) {
                $out[$field] = DB::table($table)
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        }
        if (in_array($module, ['reservations', 'loans'])) {
            $out['member_id'] = DB::table('users')
                ->where('role', 'member')
                ->when(
                    $request->user()->role === Role::Member,
                    fn ($q) => $q->where('id', $request->user()->id),
                )
                ->orderBy('name')
                ->get(['id', 'name']);
            $out['book_id'] = DB::table('books')
                ->orderBy('title')
                ->get(['id', 'title as name']);
            $out['status'] = [
                ['id' => 'pending', 'name' => 'Pending'],
                ['id' => 'ready', 'name' => 'Ready for pickup'],
                ['id' => 'cancelled', 'name' => 'Cancelled'],
            ];
            if ($module === 'loans') {
                $out['reservation_id'] = DB::table('reservations as r')
                    ->join('books as b', 'b.id', '=', 'r.books_id')
                    ->join('users as u', 'u.id', '=', 'r.users_id')
                    ->where('r.status', 'ready_for_pickup')
                    ->get(['r.id', 'b.title', 'u.name'])
                    ->map(fn ($row) => ['id' => $row->id, 'name' => $row->title.' · '.$row->name]);
            }
        }

        return response()->json((object) $out);
    }

    private function rules(string $module, ?int $id): array
    {
        $name = ['required', 'string', 'max:100', Rule::unique(self::TABLES[$module], 'name')->ignore($id)];
        $member = [
            'required',
            'integer',
            Rule::exists('users', 'id')->where('role', 'member')->where('is_active', true),
        ];

        return match ($module) {
            'categories' => ['name' => $name, 'description' => 'nullable|string|max:500'],
            'authors' => [
                'name' => $name,
                'country' => 'required|string|max:100',
                'biography' => 'nullable|string|max:1000',
            ],
            'publishers' => [
                'name' => $name,
                'city' => 'required|string|max:100',
                'email' => 'required|email|max:180',
            ],
            'members', 'librarians' => [
                'name' => 'required|string|max:100',
                'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($id)],
                'phone' => 'nullable|string|max:30',
                'address' => 'nullable|string|max:500',
                'password' => ($id ? 'nullable' : 'required').'|string|min:12|max:160',
            ],
            'books' => [
                'title' => 'required|string|max:200',
                'isbn' => ['required', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($id)],
                'category_id' => 'required|integer|exists:categories,id',
                'author_id' => 'required|integer|exists:authors,id',
                'publisher_id' => 'required|integer|exists:publishers,id',
                'published_year' => 'required|integer|min:1000|max:'.(date('Y') + 1),
                'stock' => 'required|integer|min:0|max:100000',
                'shelf' => 'required|string|max:40',
                'description' => 'nullable|string|max:500',
            ],
            'reservations' => [
                'member_id' => $member,
                'book_id' => 'required|integer|exists:books,id',
                'reserved_on' => 'required|date_format:Y-m-d|before_or_equal:today',
                'status' => 'required|in:pending,ready,cancelled',
                'notes' => 'nullable|string|max:1000',
            ],
            'loans' => [
                'member_id' => $member,
                'book_id' => 'required|integer|exists:books,id',
                'reservation_id' => 'nullable|integer|exists:reservations,id',
                'borrowed_on' => 'required|date_format:Y-m-d|before_or_equal:today',
                'due_on' => 'required|date_format:Y-m-d|after_or_equal:borrowed_on',
                'returned_on' => 'nullable|date_format:Y-m-d|after_or_equal:borrowed_on|before_or_equal:today',
                'notes' => 'nullable|string|max:1000',
            ],
        };
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['data' => $message]);
    }

    private function checkStock(): void
    {
        if (
            DB::table('books as b')
                ->whereRaw(
                    "b.total_stock < (SELECT COUNT(*) FROM loans l WHERE l.books_id=b.id AND l.status='borrowed') + (SELECT COUNT(*) FROM reservations r WHERE r.books_id=b.id AND r.status='ready_for_pickup')",
                )
                ->exists()
        ) {
            $this->fail(
                'Not enough copies are available. Return a book or cancel a ready-for-pickup reservation.',
            );
        }
    }

    public function save(Request $request, string $module, ?int $id = null)
    {
        $this->authorizeModule($request, $module, true);
        try {
            DB::transaction(function () use ($request, $module, $id) {
                if (in_array($module, ['books', 'loans', 'reservations', 'authors'])) {
                    DB::table('books')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get(['id']);
                }
                $old = $id ? $this->query($request, $module)->where('t.id', $id)->first() : null;
                abort_if($id && ! $old, 404);
                if ($module === 'reservations' && $request->user()->role === Role::Member) {
                    if ($id) {
                        if (! in_array($old->status, ['pending', 'ready_for_pickup'])) {
                            $this->fail('This reservation cannot be cancelled.');
                        }
                        DB::table('reservations')
                            ->where('id', $id)
                            ->update(['status' => 'cancelled', 'updated_at' => now()]);

                        return;
                    }
                    $request->merge(['member_id' => $request->user()->id, 'status' => 'pending']);
                }
                $v = $request->validate($this->rules($module, $id));
                foreach (['description', 'biography', 'notes', 'phone', 'address'] as $field) {
                    if (array_key_exists($field, $v)) {
                        $v[$field] ??= '';
                    }
                }
                if ($module === 'books') {
                    $v['categories_id'] = $v['category_id'];
                    unset($v['category_id']);
                    $v['total_stock'] = $v['stock'];
                    unset($v['stock']);
                    $v['author'] = DB::table('authors')->where('id', $v['author_id'])->value('name');
                } elseif (in_array($module, ['members', 'librarians'])) {
                    $v['role'] = $module === 'members' ? 'member' : 'librarian';
                    $v['email'] = strtolower($v['email']);
                    if (! empty($v['password'])) {
                        $v['password'] = Hash::make($v['password']);
                    } else {
                        unset($v['password']);
                    }
                    if (! $id) {
                        $v['is_active'] = true;
                        $v['email_verified_at'] = now();
                    }
                } elseif (in_array($module, ['loans', 'reservations'])) {
                    $v['users_id'] = (int) $v['member_id'];
                    $v['books_id'] = (int) $v['book_id'];
                    unset($v['member_id'], $v['book_id']);
                    if ($module === 'reservations') {
                        if ($old && $old->status === 'completed') {
                            $this->fail('This reservation has been collected and is locked by its loan.');
                        }
                        if (
                            DB::table('reservations')
                                ->where('users_id', $v['users_id'])
                                ->where('books_id', $v['books_id'])
                                ->whereIn('status', ['pending', 'ready_for_pickup'])
                                ->when($id, fn ($q) => $q->where('id', '!=', $id))
                                ->exists()
                        ) {
                            $this->fail('This member already has an active reservation for this book.');
                        }
                        if ($v['status'] === 'ready') {
                            $v['status'] = 'ready_for_pickup';
                            $v['processed_by'] = $request->user()->id;
                            $v['processed_at'] = now();
                            $v['pickup_deadline'] = now()->addDays(3);
                        }
                    } else {
                        $rid = empty($v['reservation_id']) ? null : (int) $v['reservation_id'];
                        unset($v['reservation_id']);
                        if ($old && $old->reservations_id !== $rid) {
                            $this->fail('The linked reservation cannot be changed.');
                        }
                        if ($rid) {
                            $reservation = DB::table('reservations')
                                ->where('id', $rid)
                                ->lockForUpdate()
                                ->first();
                            if (
                                ! $reservation ||
                                $reservation->users_id !== $v['users_id'] ||
                                $reservation->books_id !== $v['books_id'] ||
                                $reservation->status !== ($id ? 'completed' : 'ready_for_pickup')
                            ) {
                                $this->fail(
                                    'The reservation must be ready for pickup and match the book and member.',
                                );
                            }
                            if (
                                $v['borrowed_on'] <
                                ($reservation->reserved_on ?: substr($reservation->created_at, 0, 10))
                            ) {
                                $this->fail('The borrowing date cannot be earlier than the reservation date.');
                            }
                            if (! $id) {
                                DB::table('reservations')
                                    ->where('id', $rid)
                                    ->update(['status' => 'completed', 'updated_at' => now()]);
                            }
                        }
                        $v['reservations_id'] = $rid;
                        $v['borrowing_method'] = $rid ? 'reservation' : 'walk_in';
                        $v['borrow_date'] = $v['borrowed_on'];
                        $v['due_date'] = $v['due_on'];
                        $v['return_date'] = $v['returned_on'] ?? null;
                        $v['status'] = $v['return_date'] ? 'returned' : 'borrowed';
                        if (! $id) {
                            $v['librarian_id'] = $request->user()->id;
                        }
                        unset($v['borrowed_on'], $v['due_on'], $v['returned_on']);
                    }
                }
                $v['updated_at'] = now();
                if ($id) {
                    DB::table(self::TABLES[$module])->where('id', $id)->update($v);
                } else {
                    DB::table(self::TABLES[$module])->insert($v + ['created_at' => now()]);
                }
                if ($module === 'authors' && $id) {
                    DB::table('books')
                        ->where('author_id', $id)
                        ->update(['author' => $v['name']]);
                }
                if (in_array($module, ['books', 'loans', 'reservations'])) {
                    $this->checkStock();
                }
            });
        } catch (QueryException $e) {
            if (str_starts_with((string) $e->getCode(), '23')) {
                $this->fail('The record is duplicated or referenced by other data.');
            }
            throw $e;
        }

        return response()->json(['message' => 'The record has been saved.']);
    }

    public function destroy(Request $request, string $module, int $id)
    {
        $this->authorizeModule($request, $module, true);
        abort_if($request->user()->role === Role::Member, 403);
        try {
            DB::transaction(function () use ($request, $module, $id) {
                DB::table('books')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get(['id']);
                $row = $this->query($request, $module)->where('t.id', $id)->first();
                abort_unless($row, 404);
                if ($module === 'loans' && ! $row->return_date) {
                    $this->fail('Record the return before deleting this loan.');
                }
                DB::table(self::TABLES[$module])->where('id', $id)->delete();
            });
        } catch (QueryException $e) {
            if (str_starts_with((string) $e->getCode(), '23')) {
                $this->fail('This record is still referenced by a transaction or book.');
            }
            throw $e;
        }

        return response()->json(['message' => 'The record has been deleted.']);
    }

    public function dashboard(Request $request)
    {
        $loans = fn () => DB::table('loans')->when(
            $request->user()->role === Role::Member,
            fn ($q) => $q->where('users_id', $request->user()->id),
        );
        $reservations = fn () => DB::table('reservations')->when(
            $request->user()->role === Role::Member,
            fn ($q) => $q->where('users_id', $request->user()->id),
        );
        $recent = $this->query($request, 'loans')
            ->orderByDesc('t.id')
            ->limit(5)
            ->get()
            ->map(fn ($row) => $this->normalize('loans', $row));
        $popular = DB::table('books as b')
            ->leftJoin('categories as c', 'c.id', '=', 'b.categories_id')
            ->select('b.id', 'b.title', 'b.author', 'c.name as category')
            ->selectSub(
                fn ($q) => $q->from('loans')->selectRaw('COUNT(*)')->whereColumn('books_id', 'b.id'),
                'total',
            )
            ->orderByDesc('total')
            ->orderBy('b.id')
            ->limit(4)
            ->get();

        return response()->json([
            'books' => DB::table('books')->count(),
            'copies' => (int) DB::table('books')->sum('total_stock'),
            'members' => DB::table('users')->where('role', 'member')->count(),
            'active' => $loans()->where('status', 'borrowed')->count(),
            'overdue' => $loans()->where('status', 'borrowed')->where('due_date', '<', today())->count(),
            'pending' => $reservations()->where('status', 'pending')->count(),
            'returned' => $loans()->where('status', 'returned')->count(),
            'recent' => $recent,
            'popular' => $popular,
            'categories' => DB::table('categories as c')
                ->leftJoin('books as b', 'b.categories_id', '=', 'c.id')
                ->groupBy('c.id', 'c.name')
                ->select('c.name')
                ->selectRaw('COUNT(b.id) as total')
                ->orderByDesc('total')
                ->get(),
        ]);
    }
}
