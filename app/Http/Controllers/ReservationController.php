<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        Reservation::query()
            ->where('users_id', $userId)
            ->where('status', ReservationStatus::ReadyForPickup)
            ->whereNotNull('pickup_deadline')
            ->where('pickup_deadline', '<', now())
            ->update(['status' => ReservationStatus::Expired]);

        $status = $request->query('status');

        $reservations = Reservation::query()
            ->with(['book.category'])
            ->where('users_id', $userId)
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $title = 'My Reservations';
        $view = view()->exists('member.reservations.index') ? 'member.reservations.index' : 'coming-soon';

        return view($view, compact('reservations', 'status', 'title'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'books_id' => ['required', 'exists:books,id'],
        ]);

        $user = $request->user();
        $book = Book::findOrFail($validated['books_id']);

        if ($book->availableStock() <= 0) {
            return back()->with('error', 'This book is currently unavailable for reservation.');
        }

        $hasActiveReservation = Reservation::query()
            ->where('users_id', $user->id)
            ->where('books_id', $book->id)
            ->whereIn('status', [ReservationStatus::Pending, ReservationStatus::ReadyForPickup])
            ->exists();

        if ($hasActiveReservation) {
            return back()->with('error', 'You already have an active reservation for this book.');
        }

        $hasActiveLoan = Loan::query()
            ->where('users_id', $user->id)
            ->where('books_id', $book->id)
            ->where('status', LoanStatus::Borrowed)
            ->exists();

        if ($hasActiveLoan) {
            return back()->with('error', 'You are currently borrowing this book.');
        }

        Reservation::create([
            'status' => ReservationStatus::Pending,
            'users_id' => $user->id,
            'books_id' => $book->id,
        ]);

        return redirect()->route('member.reservations.index')->with('success', 'Reservation request submitted successfully.');
    }

    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_if($reservation->users_id !== $request->user()->id, 403);

        if (! in_array($reservation->status, [ReservationStatus::Pending, ReservationStatus::ReadyForPickup], true)) {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update([
            'status' => ReservationStatus::Cancelled,
        ]);

        return back()->with('success', 'Reservation cancelled successfully.');
    }
}
