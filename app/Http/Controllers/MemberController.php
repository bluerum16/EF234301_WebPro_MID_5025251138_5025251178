<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $members = User::query()
            ->where('role', Role::Member)
            ->withCount(['loans', 'reservations'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.members.index', compact('members', 'search', 'status'));
    }

    public function toggle(User $member): RedirectResponse
    {
        abort_if($member->role !== Role::Member, 404);

        $member->update([
            'is_active' => ! $member->is_active,
        ]);

        $message = $member->is_active ? 'Akun member diaktifkan.' : 'Akun member dinonaktifkan.';

        return back()->with('success', $message);
    }
}
