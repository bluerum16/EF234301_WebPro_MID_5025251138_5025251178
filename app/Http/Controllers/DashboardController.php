<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalBooks = Book::count();
        $totalMembers = User::where('role', Role::Member)->count();
        $totalLibrarians = User::where('role', Role::Librarian)->count();
        $activeLoans = Loan::where('status', LoanStatus::Borrowed)->count();
        $overdueLoans = Loan::where('status', LoanStatus::Borrowed)->where('due_date', '<', today())->count();
        $activeReservations = Reservation::whereIn('status', [ReservationStatus::Pending, ReservationStatus::ReadyForPickup])->count();

        $recentLoans = Loan::with(['book', 'member'])
            ->latest('borrow_date')
            ->take(5)
            ->get();

        $view = view()->exists('admin.dashboard') ? 'admin.dashboard' : 'dashboard';

        return view($view, compact(
            'totalBooks',
            'totalMembers',
            'totalLibrarians',
            'activeLoans',
            'overdueLoans',
            'activeReservations',
            'recentLoans'
        ));
    }
}
