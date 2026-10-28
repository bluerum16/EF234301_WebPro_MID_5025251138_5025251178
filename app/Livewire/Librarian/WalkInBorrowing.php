<?php

namespace App\Livewire\Librarian;

use App\Enums\BorrowingMethod;
use App\Enums\LoanStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::librarian')]
#[Title('Walk-In Borrowing')]
class WalkInBorrowing extends Component
{
    public ?int $users_id = null;

    public ?int $books_id = null;

    public string $borrow_date = '';

    public string $due_date = '';

    public function mount(): void
    {
        $this->borrow_date = today()->toDateString();
        $this->due_date = today()->addDays(14)->toDateString();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'users_id' => ['required', 'exists:users,id'],
            'books_id' => ['required', 'exists:books,id'],
            'borrow_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:borrow_date'],
        ];
    }

    public function save(): ?RedirectResponse
    {
        $validated = $this->validate();

        $member = User::findOrFail($this->users_id);
        if (! $member->is_active || $member->role !== Role::Member) {
            session()->flash('error', 'Selected user must be an active library member.');

            return null;
        }

        $book = Book::findOrFail($this->books_id);
        if ($book->availableStock() <= 0) {
            session()->flash('error', 'Cannot proceed, no available stock.');

            return null;
        }

        Loan::create([
            'borrowing_method' => BorrowingMethod::WalkIn,
            'borrow_date' => $validated['borrow_date'],
            'due_date' => $validated['due_date'],
            'status' => LoanStatus::Borrowed,
            'users_id' => $member->id,
            'books_id' => $book->id,
            'librarian_id' => (int) Auth::id(),
        ]);

        session()->flash('success', 'Walk-In loan recorded successfully.');

        return redirect()->route('librarian.loans.index');
    }

    public function render(): View
    {
        $members = User::query()
            ->where('role', Role::Member)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $books = Book::query()
            ->with('category')
            ->orderBy('title')
            ->get()
            ->filter(fn (Book $book) => $book->availableStock() > 0);

        return view('livewire.librarian.walk-in-borrowing', [
            'members' => $members,
            'books' => $books,
        ]);
    }
}
