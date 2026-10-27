<?php

use App\Enums\Role;
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
        Route::view('books', 'coming-soon', ['title' => 'Books'])->name('books.index');
        Route::view('reservations', 'coming-soon', ['title' => 'Reservations'])->name('reservations.index');
        Route::view('loans', 'coming-soon', ['title' => 'Loans'])->name('loans.index');
        Route::view('loans/create', 'coming-soon', ['title' => 'Walk-In Borrowing'])->name('loans.create');
    });

    Route::middleware('role:member')->name('member.')->group(function () {
        Route::view('books', 'coming-soon', ['title' => 'Book Catalog'])->name('books.index');
        Route::view('books/{book}', 'coming-soon', ['title' => 'Book Details'])->name('books.show');
        Route::view('my/reservations', 'coming-soon', ['title' => 'My Reservations'])->name('reservations.index');
        Route::view('my/loans', 'coming-soon', ['title' => 'My Borrowings'])->name('loans.index');
    });
});

require __DIR__.'/settings.php';
