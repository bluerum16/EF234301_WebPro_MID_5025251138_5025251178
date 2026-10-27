<?php

namespace App\Models;

use App\Enums\BorrowingMethod;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property BorrowingMethod $borrowing_method
 * @property Carbon $borrow_date
 * @property Carbon $due_date
 * @property Carbon|null $return_date
 * @property LoanStatus $status
 * @property int|null $reservations_id
 * @property int $users_id
 * @property int $books_id
 * @property int $librarian_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['borrowing_method', 'borrow_date', 'due_date', 'return_date', 'status', 'reservations_id', 'users_id', 'books_id', 'librarian_id'])]
class Loan extends Model
{
    /**
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservations_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'books_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function librarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'librarian_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === LoanStatus::Borrowed && $this->due_date->isBefore(today());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrowing_method' => BorrowingMethod::class,
            'status' => LoanStatus::class,
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
        ];
    }
}
