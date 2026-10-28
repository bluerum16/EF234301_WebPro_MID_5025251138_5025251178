<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $author
 * @property string $isbn
 * @property string $description
 * @property int $total_stock
 * @property int $categories_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'author', 'isbn', 'description', 'total_stock', 'categories_id'])]
class Book extends Model
{
    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'books_id');
    }

    /**
     * @return HasMany<Loan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'books_id');
    }

    /**
     * Copies that are not borrowed and not held for a pickup.
     */
    public function availableStock(): int
    {
        $borrowed = $this->loans()->where('status', LoanStatus::Borrowed)->count();
        $held = $this->reservations()->where('status', ReservationStatus::ReadyForPickup)->count();

        return max(0, $this->total_stock - $borrowed - $held);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_stock' => 'integer',
        ];
    }
}
