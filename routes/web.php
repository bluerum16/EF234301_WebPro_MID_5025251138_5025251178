<?php

use App\Enums\Role;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LibrarianController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Livewire\Librarian\BookManagement;
use App\Livewire\Librarian\LoanMonitoring;
use App\Livewire\Librarian\ReservationManagement;
use App\Livewire\Librarian\WalkInBorrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return $request->user()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        return match ($request->user()?->role) {
            Role::Admin => redirect()->route('admin.dashboard'),
            Role::Librarian => redirect()->route('librarian.loans.index'),
            default => redirect()->route('member.books.index'),
        };
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('librarians', LibrarianController::class);
        Route::get('members', [MemberController::class, 'index'])->name('members.index');
        Route::patch('members/{member}/toggle', [MemberController::class, 'toggle'])->name('members.toggle');
        Route::resource('categories', CategoryController::class);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
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
        Route::get('my/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('my/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::patch('my/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::get('my/loans', [LoanController::class, 'index'])->name('loans.index');
    });
});

require __DIR__.'/settings.php';
