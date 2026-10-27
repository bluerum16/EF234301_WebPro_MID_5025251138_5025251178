<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');

        $query = Loan::query()->with(['book', 'member', 'librarian']);

        if ($startDate) {
            $query->whereDate('borrow_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('borrow_date', '<=', $endDate);
        }

        if ($status === 'borrowed') {
            $query->where('status', LoanStatus::Borrowed);
        } elseif ($status === 'returned') {
            $query->where('status', LoanStatus::Returned);
        } elseif ($status === 'overdue') {
            $query->where('status', LoanStatus::Borrowed)->where('due_date', '<', today());
        }

        $summaryQuery = clone $query;
        $totalLoans = $summaryQuery->count();
        $totalReturned = (clone $summaryQuery)->where('status', LoanStatus::Returned)->count();
        $totalBorrowed = (clone $summaryQuery)->where('status', LoanStatus::Borrowed)->count();

        $loans = $query->latest('borrow_date')->paginate(15)->withQueryString();

        return view('admin.reports.index', compact(
            'loans',
            'startDate',
            'endDate',
            'status',
            'totalLoans',
            'totalReturned',
            'totalBorrowed'
        ));
    }
}
