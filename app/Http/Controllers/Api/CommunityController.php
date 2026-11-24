<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityController extends Controller
{
    private function book(int $book): void
    {
        abort_unless(DB::table('books')->where('id', $book)->exists(), 404, 'Book not found.');
    }

    private function member(Request $request): void
    {
        abort_unless($request->user()->role === Role::Member, 403, 'This feature is only available to members.');
    }

    public function show(Request $request, int $book)
    {
        $this->book($book);
        $page = max(1, min(100000, (int) $request->query('page', 1)));
        $reviews = DB::table('reviews')->where('books_id', $book);
        $count = (clone $reviews)->count();
        $average = (clone $reviews)->avg('rating');
        $own = (clone $reviews)
            ->where('users_id', $request->user()->id)
            ->first(['id', 'rating', 'comment', 'updated_at']);
        $rows = DB::table('reviews as r')
            ->join('users as u', 'u.id', '=', 'r.users_id')
            ->where('r.books_id', $book)
            ->orderByDesc('r.updated_at')
            ->orderByDesc('r.id')
            ->offset(($page - 1) * 10)
            ->limit(11)
            ->get(['r.id', 'r.rating', 'r.comment', 'r.updated_at', 'u.name']);

        return response()->json([
            'favorite' => DB::table('favorites')
                ->where('users_id', $request->user()->id)
                ->where('books_id', $book)
                ->exists(),
            'count' => $count,
            'average' => $average === null ? null : round((float) $average, 1),
            'own_review' => $own,
            'reviews' => $rows->take(10)->values(),
            'page' => $page,
            'has_next' => $rows->count() > 10,
        ]);
    }

    public function favorite(Request $request, int $book)
    {
        $this->member($request);
        $this->book($book);
        $where = ['users_id' => $request->user()->id, 'books_id' => $book];
        if ($request->isMethod('delete')) {
            DB::table('favorites')->where($where)->delete();
        } else {
            DB::table('favorites')->insertOrIgnore($where + ['created_at' => now(), 'updated_at' => now()]);
        }

        return response()->json([
            'message' => $request->isMethod('delete')
                ? 'The book has been removed from your favorites.'
                : 'The book has been added to your favorites.',
        ]);
    }

    public function review(Request $request, int $book)
    {
        $this->member($request);
        $this->book($book);
        $query = DB::table('reviews')->where('users_id', $request->user()->id)->where('books_id', $book);
        if ($request->isMethod('delete')) {
            abort_unless($query->delete(), 404, 'Your review was not found.');

            return response()->json(['message' => 'Your review has been deleted.']);
        }
        $v = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|max:1000',
        ]);
        try {
            if ($request->isMethod('put')) {
                abort_unless((clone $query)->exists(), 404, 'Your review was not found.');
                $query->update($v + ['updated_at' => now()]);
            } else {
                DB::table('reviews')->insert(
                    $v + [
                        'users_id' => $request->user()->id,
                        'books_id' => $book,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        } catch (QueryException $e) {
            if (str_starts_with((string) $e->getCode(), '23')) {
                throw ValidationException::withMessages([
                    'review' => 'You have already reviewed this book. Edit your existing review.',
                ]);
            }
            throw $e;
        }

        return response()->json(['message' => 'Your review has been saved.']);
    }
}
