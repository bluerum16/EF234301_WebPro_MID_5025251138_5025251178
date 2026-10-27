@php
    $onTimeLoans = max(0, $activeLoans - $overdueLoans);
    $onTimeRate = $activeLoans > 0 ? (int) round(($onTimeLoans / $activeLoans) * 100) : 0;
    $overdueRate = $activeLoans > 0 ? 100 - $onTimeRate : 0;
@endphp

<x-layouts::admin title="Dashboard" subtitle="A quick look at your library, its collections, and today's circulation.">
    <x-slot:actions>
        <a href="{{ route('admin.librarians.create') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
            Add librarian
        </a>
    </x-slot:actions>

    <section class="rounded-lg bg-brand-700 px-6 py-5 text-white">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-brand-100">
                    {{ now('Asia/Jakarta')->format('l, d F Y') }}
                </p>

                <h2 class="mt-1 text-xl font-semibold tracking-tight">Library overview</h2>

                <p class="mt-1 text-sm text-brand-100">
                    {{ $activeLoans }} active {{ \Illuminate\Support\Str::plural('loan', $activeLoans) }},
                    {{ $overdueLoans }} overdue, and
                    {{ $activeReservations }} {{ \Illuminate\Support\Str::plural('reservation', $activeReservations) }} to watch.
                </p>
            </div>

            <a href="{{ route('admin.reports.index') }}" class="inline-flex shrink-0 items-center self-start rounded-lg border border-white/30 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10 sm:self-auto">
                View reports
            </a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Library summary">
        <x-admin.stat-card label="Total books" :value="$totalBooks" hint="Titles recorded in the catalog" />
        <x-admin.stat-card label="Members" :value="$totalMembers" hint="Registered library members" />
        <x-admin.stat-card label="Librarians" :value="$totalLibrarians" hint="Staff accounts with librarian access" />
        <x-admin.stat-card label="Active loans" :value="$activeLoans" hint="Books currently borrowed" />
        <x-admin.stat-card label="Overdue loans" :value="$overdueLoans" hint="Borrowed past their due date" />
        <x-admin.stat-card label="Reservations" :value="$activeReservations" hint="Pending or ready for pickup" />
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900">Recent loans</h2>
                    <p class="mt-0.5 text-sm text-zinc-500">The latest borrowing activity in the library.</p>
                </div>

                <a href="{{ route('admin.reports.index') }}" class="inline-flex shrink-0 items-center text-sm font-semibold text-brand-600 transition hover:text-brand-700">
                    View report
                </a>
            </div>

            @if ($recentLoans->isEmpty())
                <x-admin.empty-state title="No loans recorded yet" description="Borrowing activity will appear here once the librarian records a walk-in loan or confirms a reservation pickup." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Book</th>
                                <th scope="col" class="px-5 py-3">Member</th>
                                <th scope="col" class="px-5 py-3">Borrowed</th>
                                <th scope="col" class="px-5 py-3">Due</th>
                                <th scope="col" class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-100">
                            @foreach ($recentLoans as $loan)
                                @php($overdue = $loan->status === \App\Enums\LoanStatus::Borrowed && $loan->due_date->isBefore(today()))

                                <tr class="transition hover:bg-zinc-50/60">
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-zinc-900">{{ $loan->book?->title ?? '—' }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">{{ $loan->book?->author }}</p>
                                    </td>

                                    <td class="px-5 py-3.5 text-zinc-600">{{ $loan->member?->name ?? '—' }}</td>

                                    <td class="whitespace-nowrap px-5 py-3.5 text-zinc-500">{{ $loan->borrow_date?->format('d M Y') ?? '—' }}</td>

                                    <td class="whitespace-nowrap px-5 py-3.5 text-zinc-500">{{ $loan->due_date?->format('d M Y') ?? '—' }}</td>

                                    <td class="px-5 py-3.5 text-right">
                                        @if ($loan->status === \App\Enums\LoanStatus::Returned)
                                            <x-admin.badge tone="zinc">Returned</x-admin.badge>
                                        @elseif ($overdue)
                                            <x-admin.badge tone="rose">Overdue</x-admin.badge>
                                        @else
                                            <x-admin.badge tone="emerald">Borrowed</x-admin.badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="rounded-lg border border-zinc-200/80 bg-white p-5 shadow-sm">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900">Circulation health</h2>
                    <p class="mt-0.5 text-sm text-zinc-500">Active loans at a glance.</p>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-zinc-600">On time</span>
                            <span class="font-semibold text-zinc-900">{{ $onTimeLoans }} / {{ $activeLoans }}</span>
                        </div>

                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-zinc-100">
                            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $onTimeRate }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-zinc-600">Overdue</span>
                            <span class="font-semibold text-zinc-900">{{ $overdueLoans }} / {{ $activeLoans }}</span>
                        </div>

                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-zinc-100">
                            <div class="h-full rounded-full bg-rose-500 transition-all" style="width: {{ $overdueRate }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-lg bg-amber-50 p-3.5 ring-1 ring-inset ring-amber-100">
                    <p class="text-xs font-semibold text-amber-900">
                        {{ $overdueLoans }} {{ \Illuminate\Support\Str::plural('loan', $overdueLoans) }} need follow-up
                    </p>
                    <p class="mt-0.5 text-xs leading-5 text-amber-700">
                        Check the reports page to see which books are past their due date.
                    </p>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200/80 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-zinc-900">Quick actions</h2>
                <p class="mt-0.5 text-sm text-zinc-500">Common administrative tasks.</p>

                <div class="mt-4 space-y-1.5">
                    <a href="{{ route('admin.librarians.create') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                        Add librarian
                    </a>

                    <a href="{{ route('admin.categories.create') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                        Add category
                    </a>

                    <a href="{{ route('admin.members.index') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                        Manage members
                    </a>

                    <a href="{{ route('admin.reports.index') }}" class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                        View reports
                    </a>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Reservations</p>

                <p class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900">{{ $activeReservations }}</p>
                <p class="mt-1 text-xs leading-5 text-zinc-500">Pending or ready for pickup. Librarians process these from their reservation queue.</p>
            </section>
        </aside>
    </div>
</x-layouts::admin>
