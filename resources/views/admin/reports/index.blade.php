@php
    $returnRate = $totalLoans > 0 ? (int) round(($totalReturned / $totalLoans) * 100) : 0;

    $rangeLabel = 'All dates';

    if ($startDate && $endDate) {
        $rangeLabel = \Illuminate\Support\Carbon::parse($startDate)->format('d M Y').' – '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y');
    } elseif ($startDate) {
        $rangeLabel = 'From '.\Illuminate\Support\Carbon::parse($startDate)->format('d M Y');
    } elseif ($endDate) {
        $rangeLabel = 'Until '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y');
    }

    $quickRanges = [
        ['label' => 'This month', 'start' => now('Asia/Jakarta')->startOfMonth(), 'end' => now('Asia/Jakarta')->endOfMonth()],
        ['label' => 'Last 30 days', 'start' => now('Asia/Jakarta')->subDays(29), 'end' => now('Asia/Jakarta')],
        ['label' => 'This year', 'start' => now('Asia/Jakarta')->startOfYear(), 'end' => now('Asia/Jakarta')->endOfYear()],
    ];
@endphp

<x-layouts::admin title="Reports" subtitle="Review borrowing and return activity across the library, filtered by date and loan status.">
    <x-slot:actions>
        <button type="button" onclick="window.print()" class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
            Print report
        </button>
    </x-slot:actions>

    <div class="mb-5 hidden print:block">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-500">{{ config('app.name', 'myBook') }} · Library report</p>
        <h2 class="mt-1 text-xl font-semibold text-zinc-900">Borrowing & return activity</h2>
        <p class="mt-1 text-sm text-zinc-500">Period: {{ $rangeLabel }} · {{ $loans->total() }} records</p>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="rounded-lg border border-zinc-200/80 bg-white p-5 shadow-sm print:hidden">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">
            <div class="xl:col-span-3">
                <label for="start_date" class="block text-sm font-medium text-zinc-700">From</label>

                <input
                    id="start_date"
                    name="start_date"
                    type="date"
                    value="{{ $startDate }}"
                    class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                />
            </div>

            <div class="xl:col-span-3">
                <label for="end_date" class="block text-sm font-medium text-zinc-700">To</label>

                <input
                    id="end_date"
                    name="end_date"
                    type="date"
                    value="{{ $endDate }}"
                    class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                />
            </div>

            <div class="xl:col-span-3">
                <label for="status" class="block text-sm font-medium text-zinc-700">Status</label>

                <select
                    id="status"
                    name="status"
                    class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                >
                    <option value="">All statuses</option>
                    <option value="borrowed" @selected($status === 'borrowed')>Borrowed</option>
                    <option value="returned" @selected($status === 'returned')>Returned</option>
                    <option value="overdue" @selected($status === 'overdue')>Overdue</option>
                </select>
            </div>

            <div class="flex items-center gap-2 sm:col-span-2 xl:col-span-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
                    Apply filters
                </button>

                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-600 shadow-sm transition hover:bg-zinc-50 hover:text-zinc-900">
                    Reset
                </a>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Quick range</span>

            @foreach ($quickRanges as $range)
                <a
                    href="{{ route('admin.reports.index', array_filter(['start_date' => $range['start']->toDateString(), 'end_date' => $range['end']->toDateString(), 'status' => $status])) }}"
                    class="rounded-lg bg-zinc-100 px-2.5 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-200 hover:text-zinc-900"
                >
                    {{ $range['label'] }}
                </a>
            @endforeach

            @if ($startDate || $endDate || $status)
                <span class="ml-auto inline-flex items-center gap-1.5 text-xs text-zinc-500">
                    
                    {{ $rangeLabel }}{{ $status ? ' · '.ucfirst($status) : '' }}
                </span>
            @endif
        </div>
    </form>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Report summary">
        <x-admin.stat-card label="Matching loans" :value="$totalLoans" hint="Borrowing records in this period" />
        <x-admin.stat-card label="Returned" :value="$totalReturned" hint="Books already returned" />
        <x-admin.stat-card label="Still borrowed" :value="$totalBorrowed" hint="Includes overdue loans" />
        <x-admin.stat-card label="Return rate" :value="$returnRate.'%'" hint="Returned vs. matching loans" />
    </section>

    <div class="mt-6 overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-zinc-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">Borrowing & return activity</h2>
                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $rangeLabel }}{{ $status ? ' · '.ucfirst($status) : '' }} · {{ $loans->total() }} {{ \Illuminate\Support\Str::plural('record', $loans->total()) }}
                </p>
            </div>

            <p class="text-xs text-zinc-400">Dates are based on the borrow date.</p>
        </div>

        @if ($loans->isEmpty())
            <x-admin.empty-state title="No borrowing activity found" description="Try widening the date range or clearing the status filter to see more records.">
                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Clear filters
                </a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Book</th>
                            <th scope="col" class="px-5 py-3">Member</th>
                            <th scope="col" class="px-5 py-3">Librarian</th>
                            <th scope="col" class="px-5 py-3">Borrowed</th>
                            <th scope="col" class="px-5 py-3">Due</th>
                            <th scope="col" class="px-5 py-3">Returned</th>
                            <th scope="col" class="px-5 py-3">Method</th>
                            <th scope="col" class="px-5 py-3 text-right">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($loans as $loan)
                            @php($overdue = $loan->status === \App\Enums\LoanStatus::Borrowed && $loan->due_date->isBefore(today()))

                            <tr class="transition hover:bg-zinc-50/60">
                                <td class="max-w-64 px-5 py-4">
                                    <p class="truncate font-medium text-zinc-900">{{ $loan->book?->title ?? '—' }}</p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $loan->book?->author }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="truncate text-zinc-700">{{ $loan->member?->name ?? '—' }}</p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $loan->member?->email }}</p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500">{{ $loan->librarian?->name ?? '—' }}</td>

                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500">{{ $loan->borrow_date?->format('d M Y') ?? '—' }}</td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="{{ $overdue ? 'font-semibold text-rose-600' : 'text-zinc-500' }}">
                                        {{ $loan->due_date?->format('d M Y') ?? '—' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500">{{ $loan->return_date?->format('d M Y') ?? '—' }}</td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-md bg-zinc-100 px-2 py-1 text-[11px] font-medium text-zinc-600">
                                        {{ $loan->borrowing_method?->label() ?? '—' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
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

            <div class="print:hidden">
                <x-admin.pagination :paginator="$loans" />
            </div>
        @endif
    </div>

    <p class="mt-4 text-xs text-zinc-400">
        Report generated on {{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB. Overdue status is calculated against today's date.
    </p>
</x-layouts::admin>
