<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ReservationStatus $status
 * @property Carbon|null $pickup_deadline
 * @property Carbon|null $processed_at
 * @property int $users_id
 * @property int $books_id
 * @property int|null $processed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['status', 'pickup_deadline', 'processed_at', 'users_id', 'books_id', 'processed_by'])]
class Reservation extends Model
{
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
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * @return HasOne<Loan, $this>
     */
    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class, 'reservations_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'pickup_deadline' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
