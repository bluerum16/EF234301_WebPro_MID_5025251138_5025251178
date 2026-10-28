<?php

namespace App\Livewire\Librarian;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::librarian')]
#[Title('Book Management')]
class BookManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedCategory = null;

    public ?int $editingBookId = null;

    public string $title = '';

    public string $author = '';

    public string $isbn = '';

    public string $description = '';

    public int $total_stock = 1;

    public ?int $categories_id = null;

    public bool $showModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $books = Book::query()
            ->with('category')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('author', 'like', '%'.$this->search.'%')
                        ->orWhere('isbn', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('categories_id', $this->selectedCategory);
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.librarian.book-management', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:30', Rule::unique('books', 'isbn')->ignore($this->editingBookId)],
            'description' => ['nullable', 'string'],
            'total_stock' => ['required', 'integer', 'min:1'],
            'categories_id' => ['required', 'exists:categories,id'],
        ];
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingBookId', 'title', 'author', 'isbn', 'description', 'total_stock', 'categories_id']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->reset(['editingBookId', 'title', 'author', 'isbn', 'description', 'total_stock', 'categories_id']);
        $this->total_stock = 1;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $book = Book::findOrFail($id);

        $this->editingBookId = $book->id;
        $this->title = $book->title;
        $this->author = $book->author;
        $this->isbn = $book->isbn;
        $this->description = $book->description ?? '';
        $this->total_stock = $book->total_stock;
        $this->categories_id = $book->categories_id;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingBookId) {
            $book = Book::findOrFail($this->editingBookId);
            $book->update($validated);
            session()->flash('success', 'Book updated successfully.');
        } else {
            Book::create($validated);
            session()->flash('success', 'Book created successfully.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $book = Book::findOrFail($id);

        $hasLoans = $book->loans()->exists();
        $hasReservations = $book->reservations()->exists();

        if ($hasLoans || $hasReservations) {
            session()->flash('error', 'Cannot delete this book because it has loan or reservation history.');

            return;
        }

        $book->delete();
        session()->flash('success', 'Book deleted successfully.');
    }
}
