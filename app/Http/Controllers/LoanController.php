<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $tab = $request->query('tab', 'active');

        $activeLoans = Loan::query()
            ->with(['book.category'])
            ->where('users_id', $userId)
            ->where('status', LoanStatus::Borrowed)
            ->orderBy('due_date')
            ->get();

        $historyLoans = Loan::query()
            ->with(['book.category'])
            ->where('users_id', $userId)
            ->where('status', LoanStatus::Returned)
            ->latest('return_date')
            ->paginate(10)
            ->withQueryString();

        $title = 'My Borrowings';
        $view = view()->exists('member.loans.index') ? 'member.loans.index' : 'coming-soon';

        return view($view, compact('activeLoans', 'historyLoans', 'tab', 'title'));
    }
}
