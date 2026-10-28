<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class LibrarianController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $librarians = User::query()
            ->where('role', Role::Librarian)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.librarians.index', compact('librarians', 'search'));
    }

    public function create(): View
    {
        return view('admin.librarians.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::Librarian,
            'is_active' => true,
        ]);

        return redirect()->route('admin.librarians.index')->with('success', 'Librarian account created successfully.');
    }

    public function edit(User $librarian): View
    {
        abort_if($librarian->role !== Role::Librarian, 404);

        return view('admin.librarians.edit', compact('librarian'));
    }

    public function update(Request $request, User $librarian): RedirectResponse
    {
        abort_if($librarian->role !== Role::Librarian, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($librarian->id)],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ]);

        $librarian->name = $validated['name'];
        $librarian->email = $validated['email'];

        if (! empty($validated['password'])) {
            $librarian->password = Hash::make($validated['password']);
        }

        $librarian->save();

        return redirect()->route('admin.librarians.index')->with('success', 'Librarian account updated successfully.');
    }

    public function destroy(User $librarian): RedirectResponse
    {
        abort_if($librarian->role !== Role::Librarian, 404);

        $hasLoans = Loan::where('librarian_id', $librarian->id)->exists();
        $hasReservations = Reservation::where('processed_by', $librarian->id)->exists();

        if ($hasLoans || $hasReservations) {
            return redirect()->route('admin.librarians.index')->with('error', 'Librarian cannot be deleted because they have associated transaction records.');
        }

        $librarian->delete();

        return redirect()->route('admin.librarians.index')->with('success', 'Librarian account deleted successfully.');
    }
}
