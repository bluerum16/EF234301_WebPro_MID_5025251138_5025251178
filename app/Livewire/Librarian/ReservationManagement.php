<?php

namespace App\Livewire\Librarian;

use App\Enums\BorrowingMethod;
use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::librarian')]
#[Title('Reservation Management')]
class ReservationManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'pending';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== ReservationStatus::Pending) {
            session()->flash('error', 'Only pending reservations can be approved.');

            return;
        }

        if ($reservation->book->availableStock() <= 0) {
            session()->flash('error', 'Cannot approve, this book has no available stock.');

            return;
        }

        $reservation->update([
            'status' => ReservationStatus::ReadyForPickup,
            'pickup_deadline' => now()->addDays(2),
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        session()->flash('success', 'Reservation approved and ready for pick up.');
    }

    public function reject(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== ReservationStatus::Pending) {
            session()->flash('error', 'Only pending reservations can be rejected.');

            return;
        }

        $reservation->update([
            'status' => ReservationStatus::Rejected,
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        session()->flash('success', 'Reservation has successfully rejected.');
    }

    public function confirmPickup(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== ReservationStatus::ReadyForPickup) {
            session()->flash('error', 'Only accepted reservations can be confirmed.');

            return;
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => ReservationStatus::Completed,
            ]);

            Loan::create([
                'borrowing_method' => BorrowingMethod::Reservation,
                'borrow_date' => now(),
                'due_date' => now()->addDays(14),
                'status' => LoanStatus::Borrowed,
                'reservations_id' => $reservation->id,
                'users_id' => $reservation->users_id,
                'books_id' => $reservation->books_id,
                'librarian_id' => Auth::id(),
            ]);
        });

        session()->flash('success', 'Pickup confirmed, loan record created successfully.');
    }

    public function render(): View
    {
        $reservations = Reservation::query()
            ->with(['member', 'book', 'processor'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('book', function ($bookQuery) {
                        $bookQuery->where('title', 'like', '%'.$this->search.'%')
                            ->orWhere('isbn', 'like', '%'.$this->search.'%');
                    })->orWhereHas('member', function ($memberQuery) {
                        $memberQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.librarian.reservation-management', [
            'reservations' => $reservations,
            'pendingCount' => Reservation::where('status', ReservationStatus::Pending)->count(),
            'readyCount' => Reservation::where('status', ReservationStatus::ReadyForPickup)->count(),
        ]);
    }
}
