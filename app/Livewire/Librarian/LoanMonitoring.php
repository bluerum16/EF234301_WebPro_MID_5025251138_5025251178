<?php

namespace App\Livewire\Librarian;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Loan Monitoring')]
class LoanMonitoring extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function returnBook(int $loanId): void
    {
        $loan = Loan::findOrFail($loanId);

        if ($loan->status === LoanStatus::Returned) {
            session()->flash('error', 'This loan has already returned.');

            return;
        }

        $loan->update([
            'status' => LoanStatus::Returned,
            'return_date' => today(),
        ]);

        session()->flash('success', 'Book returned successfully.');
    }

    public function render(): View
    {
        $today = today();

        $activeCount = Loan::where('status', LoanStatus::Borrowed)
            ->where('due_date', '>=', $today)
            ->count();

        $overdueCount = Loan::where('status', LoanStatus::Borrowed)
            ->where('due_date', '<', $today)
            ->count();

        $returnedCount = Loan::where('status', LoanStatus::Returned)->count();

        $loans = Loan::query()
            ->with(['member', 'book', 'librarian'])
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
            ->when($this->statusFilter === 'active', function ($query) use ($today) {
                $query->where('status', LoanStatus::Borrowed)
                    ->where('due_date', '>=', $today);
            })
            ->when($this->statusFilter === 'overdue', function ($query) use ($today) {
                $query->where('status', LoanStatus::Borrowed)
                    ->where('due_date', '<', $today);
            })
            ->when($this->statusFilter === 'returned', function ($query) {
                $query->where('status', LoanStatus::Returned);
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.librarian.loan-monitoring', [
            'loans' => $loans,
            'activeCount' => $activeCount,
            'overdueCount' => $overdueCount,
            'returnedCount' => $returnedCount,
        ]);
    }
}
