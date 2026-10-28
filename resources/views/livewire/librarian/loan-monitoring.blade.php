<x-layouts::librarian title="Loan Monitoring" subtitle="Track all library borrowings, monitor overdue loans, and process returns.">
    <x-slot:actions>
        <a href="{{ route('librarian.loans.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm
        transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
            + New walk-in loan
        </a>
    </x-slot:actions>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-admin.stat-card
            label="Active Loans"
            :value="$activeCount"
            hint="Currently borrowed by members"
        />

        <x-admin.stat-card
            label="Overdue Loans"
            :value="$overdueCount"
            hint="Past due date without return"
        />

        <x-admin.stat-card
            label="Returned Books"
            :value="$returnedCount"
            hint="Total completed returns"
        />
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $tabs = [
                        'all' => 'All Loans',
                        'active' => 'Active',
                        'overdue' => 'Overdue',
                        'returned' => 'Returned',
                    ];
                @endphp

                @foreach ($tabs as $key => $label)
                    <button
                        type="button"
                        wire:click="$set('statusFilter', '{{ $key }}')"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === $key ? 'bg-brand-50 text-brand-700 ring-1 ring-inset
                        ring-brand-200/70' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search member or book..."
                        class="w-full rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-8 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400
                        focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 sm:w-64"
                    />
                    <div wire:loading wire:target="search" class="absolute right-2.5 top-2.5 text-zinc-400">
                        <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-zinc-300 border-t-brand-600"></span>
                    </div>
                </div>

                @if ($search !== '')
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="text-xs font-medium text-zinc-500 hover:text-zinc-800"
                    >
                        Clear
                    </button>
                @endif
            </div>
        </div>

        @if ($loans->isEmpty())
            <x-admin.empty-state
                title="{{ $search ? 'No loans match your search' : 'No loans found' }}"
                description="{{ $search ? 'Try searching for a different member name or book title.' : 'There are currently no borrowing records in this category.' }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Borrower</th>
                            <th scope="col" class="px-5 py-3">Book</th>
                            <th scope="col" class="px-5 py-3">Method</th>
                            <th scope="col" class="px-5 py-3">Period</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($loans as $loan)
                            <tr class="transition hover:bg-zinc-50/60">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-admin.avatar :initials="$loan->member->initials()" size="sm" tone="zinc" />
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-zinc-900">{{ $loan->member->name }}</p>
                                            <p class="truncate text-xs text-zinc-500">{{ $loan->member->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="min-w-0 max-w-xs">
                                        <p class="truncate font-medium text-zinc-900">{{ $loan->book->title }}</p>
                                        <p class="truncate text-xs text-zinc-500">{{ $loan->book->author }} · {{ $loan->book->isbn }}</p>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-admin.badge :tone="$loan->borrowing_method === \App\Enums\BorrowingMethod::WalkIn ? 'sky' : 'brand'">
                                        {{ $loan->borrowing_method->label() }}
                                    </x-admin.badge>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-xs">
                                        <p class="text-zinc-700">Borrowed: {{ $loan->borrow_date->format('d M Y') }}</p>
                                        <p class="{{ $loan->isOverdue() ? 'font-semibold text-rose-600' : 'text-zinc-500' }}">
                                            Due: {{ $loan->due_date->format('d M Y') }}
                                            @if ($loan->isOverdue())
                                                (Overdue)
                                            @endif
                                        </p>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($loan->status === \App\Enums\LoanStatus::Returned)
                                        <x-admin.badge tone="zinc">
                                            Returned on {{ $loan->return_date?->format('d M Y') }}
                                        </x-admin.badge>
                                    @elseif ($loan->isOverdue())
                                        <x-admin.badge tone="rose">
                                            Overdue
                                        </x-admin.badge>
                                    @else
                                        <x-admin.badge tone="emerald">
                                            Borrowed
                                        </x-admin.badge>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if ($loan->status === \App\Enums\LoanStatus::Borrowed)
                                        <button
                                            type="button"
                                            wire:click="returnBook({{ $loan->id }})"
                                            wire:confirm="Confirm return for '{{ $loan->book->title }}' borrowed by {{ $loan->member->name }}?"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700
                                            ￹shadow-sm transition hover:bg-zinc-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                        >
                                            Process return
                                        </button>
                                    @else
                                        <span class="text-xs text-zinc-400">Completed</span>
                                    @endif
                                </td>
                </tr>
                        @endforeach
            </tbody>
                </table>
            </div>

            @if ($loans->hasPages())
                <div class="border-t border-zinc-100 px-5 py-3">
            {{ $loans->links() }}
                </div>
            @endif
        @endif
    </div>

</x-layouts::librarian>