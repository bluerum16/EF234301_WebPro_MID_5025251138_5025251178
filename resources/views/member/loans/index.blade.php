@php($currentTab = $tab === 'history' ? 'history' : 'active')

<x-layouts::member title="My borrowings" subtitle="View your active loans and your borrowing history.">
    <div class="flex flex-wrap items-center gap-2">
        <a
            href="{{ route('member.loans.index', ['tab' => 'active']) }}"
            @if ($currentTab === 'active') aria-current="page" @endif
            class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $currentTab === 'active' ? 'bg-brand-600 text-white' : 'bg-white text-zinc-600 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-100 hover:text-zinc-900' }}"
        >
            Active ({{ $activeLoans->count() }})
        </a>

        <a
            href="{{ route('member.loans.index', ['tab' => 'history']) }}"
            @if ($currentTab === 'history') aria-current="page" @endif
            class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $currentTab === 'history' ? 'bg-brand-600 text-white' : 'bg-white text-zinc-600 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-100 hover:text-zinc-900' }}"
        >
            History ({{ $historyLoans->total() }})
        </a>
    </div>

    @if ($currentTab === 'history')
        @if ($historyLoans->isEmpty())
            <div class="mt-5">
                <x-member.empty-state title="No borrowing history yet" description="Books you have borrowed and returned will appear here." />
            </div>
        @else
            <div class="mt-5 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Book</th>
                                <th scope="col" class="px-5 py-3">Borrowed</th>
                                <th scope="col" class="px-5 py-3">Returned</th>
                                <th scope="col" class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-100">
                            @foreach ($historyLoans as $loan)
                                <tr class="transition hover:bg-zinc-50/60">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-zinc-900">{{ $loan->book?->title ?? '—' }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">
                                            {{ $loan->book?->author }}
                                            @if ($loan->book?->category)
                                                · {{ $loan->book->category->name }}
                                            @endif
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-500">{{ $loan->borrow_date?->format('d M Y') ?? '—' }}</td>

                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-500">{{ $loan->return_date?->format('d M Y') ?? '—' }}</td>

                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <x-member.badge tone="zinc">Returned</x-member.badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <x-member.pagination :paginator="$historyLoans" />
        @endif
    @else
        @if ($activeLoans->isEmpty())
            <div class="mt-5">
                <x-member.empty-state title="No active borrowings" description="Books you are currently borrowing will appear here.">
                    @if (view()->exists('member.books.index'))
                        <a href="{{ route('member.books.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            Browse the catalog
                        </a>
                    @endif
                </x-member.empty-state>
            </div>
        @else
            <div class="mt-5 space-y-3">
                @foreach ($activeLoans as $loan)
                    @php($overdue = $loan->isOverdue())

                    <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <x-member.book-thumb :book="$loan->book" />

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Loan #{{ $loan->id }}</p>
                                        <h2 class="mt-1 truncate text-base font-semibold text-zinc-900">{{ $loan->book?->title ?? 'Book' }}</h2>
                                        <p class="mt-0.5 truncate text-sm text-zinc-500">
                                            {{ $loan->book?->author }}
                                            @if ($loan->book?->category)
                                                · {{ $loan->book->category->name }}
                                            @endif
                                        </p>
                                    </div>

                                    <x-member.badge :tone="$overdue ? 'rose' : 'emerald'">
                                        {{ $overdue ? 'Overdue' : 'Borrowed' }}
                                    </x-member.badge>
                                </div>

                                <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs">
                                    <div>
                                        <dt class="text-zinc-400">Borrowed</dt>
                                        <dd class="mt-0.5 font-medium text-zinc-700">{{ $loan->borrow_date?->format('d M Y') ?? '—' }}</dd>
                                    </div>

                                    <div>
                                        <dt class="text-zinc-400">Due date</dt>
                                        <dd class="mt-0.5 font-medium {{ $overdue ? 'text-rose-600' : 'text-zinc-700' }}">
                                            {{ $loan->due_date?->format('d M Y') ?? '—' }}
                                        </dd>
                                    </div>
                                </dl>

                                @if ($overdue)
                                    <p class="mt-4 rounded-md bg-rose-50 px-3 py-2 text-xs text-rose-800">This book is overdue. Please return it to the library as soon as possible.</p>
                                @else
                                    <p class="mt-4 rounded-md bg-zinc-50 px-3 py-2 text-xs text-zinc-600">Return this book at the library before the due date.</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @endif
</x-layouts::member>
