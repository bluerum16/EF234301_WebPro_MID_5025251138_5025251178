<?php

use App\Enums\Role;
use App\Livewire\Librarian\BookManagement;
use App\Livewire\Librarian\LoanMonitoring;
use App\Livewire\Librarian\ReservationManagement;
use App\Livewire\Librarian\WalkInBorrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        return match ($request->user()?->role) {
            Role::Admin => redirect()->route('admin.dashboard'),
            Role::Librarian => redirect()->route('librarian.loans.index'),
            default => redirect()->route('member.books.index'),
        };
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
        Route::view('librarians', 'coming-soon', ['title' => 'Librarians'])->name('librarians.index');
        Route::view('members', 'coming-soon', ['title' => 'Members'])->name('members.index');
        Route::view('categories', 'coming-soon', ['title' => 'Categories'])->name('categories.index');
        Route::view('reports', 'coming-soon', ['title' => 'Reports'])->name('reports.index');
    });

    Route::middleware('role:librarian')->prefix('librarian')->name('librarian.')->group(function () {
        Route::get('books', BookManagement::class)->name('books.index');
        Route::get('reservations', ReservationManagement::class)->name('reservations.index');
        Route::get('loans', LoanMonitoring::class)->name('loans.index');
        Route::get('loans/create', WalkInBorrowing::class)->name('loans.create');
    });

    Route::middleware('role:member')->name('member.')->group(function () {
        Route::view('books', 'coming-soon', ['title' => 'Book Catalog'])->name('books.index');
        Route::view('books/{book}', 'coming-soon', ['title' => 'Book Details'])->name('books.show');
        Route::view('my/reservations', 'coming-soon', ['title' => 'My Reservations'])->name('reservations.index');
        Route::view('my/loans', 'coming-soon', ['title' => 'My Borrowings'])->name('loans.index');
    });
});

require __DIR__.'/settings.php';
