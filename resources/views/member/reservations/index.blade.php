@php
    $tabs = [
        'all' => 'All',
        \App\Enums\ReservationStatus::Pending->value => 'Pending',
        \App\Enums\ReservationStatus::ReadyForPickup->value => 'Ready for pickup',
        \App\Enums\ReservationStatus::Completed->value => 'Completed',
        \App\Enums\ReservationStatus::Rejected->value => 'Rejected',
        \App\Enums\ReservationStatus::Cancelled->value => 'Cancelled',
        \App\Enums\ReservationStatus::Expired->value => 'Expired',
    ];

    $currentStatus = $status ?: 'all';
@endphp

<x-layouts::member title="My reservations" subtitle="Track your reservation requests and cancel them before pickup.">
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ route('member.reservations.index', $key === 'all' ? [] : ['status' => $key]) }}"
                @if ($currentStatus === $key) aria-current="page" @endif
                class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ $currentStatus === $key ? 'bg-brand-600 text-white' : 'bg-white text-zinc-600 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-100 hover:text-zinc-900' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($reservations->isEmpty())
        <div class="mt-5">
            <x-member.empty-state
                title="{{ $currentStatus === 'all' ? 'No reservations yet' : 'No reservations with this status' }}"
                description="{{ $currentStatus === 'all' ? 'When you reserve a book from the catalog, it will appear here.' : 'Try another status filter to see your reservations.' }}"
            >
                @if (view()->exists('member.books.index'))
                    <a href="{{ route('member.books.index') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Browse the catalog
                    </a>
                @endif
            </x-member.empty-state>
        </div>
    @else
        <div class="mt-5 space-y-3">
            @foreach ($reservations as $reservation)
                @php
                    $tone = match ($reservation->status) {
                        \App\Enums\ReservationStatus::Pending => 'amber',
                        \App\Enums\ReservationStatus::ReadyForPickup => 'blue',
                        \App\Enums\ReservationStatus::Completed => 'emerald',
                        \App\Enums\ReservationStatus::Rejected, \App\Enums\ReservationStatus::Expired => 'rose',
                        default => 'zinc',
                    };

                    $canCancel = in_array($reservation->status, [
                        \App\Enums\ReservationStatus::Pending,
                        \App\Enums\ReservationStatus::ReadyForPickup,
                    ], true);
                @endphp

                <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <x-member.book-thumb :book="$reservation->book" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Reservation #{{ $reservation->id }}</p>
                                    <h2 class="mt-1 truncate text-base font-semibold text-zinc-900">{{ $reservation->book?->title ?? 'Book' }}</h2>
                                    <p class="mt-0.5 truncate text-sm text-zinc-500">
                                        {{ $reservation->book?->author }}
                                        @if ($reservation->book?->category)
                                            · {{ $reservation->book->category->name }}
                                        @endif
                                    </p>
                                </div>

                                <x-member.badge :tone="$tone">{{ $reservation->status->label() }}</x-member.badge>
                            </div>

                            <dl class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-zinc-400">Requested</dt>
                                    <dd class="mt-0.5 font-medium text-zinc-700">{{ $reservation->created_at?->format('d M Y') ?? '—' }}</dd>
                                </div>

                                @if ($reservation->processed_at)
                                    <div>
                                        <dt class="text-zinc-400">Processed</dt>
                                        <dd class="mt-0.5 font-medium text-zinc-700">{{ $reservation->processed_at->format('d M Y') }}</dd>
                                    </div>
                                @endif

                                @if ($reservation->pickup_deadline)
                                    <div>
                                        <dt class="text-zinc-400">Pickup by</dt>
                                        <dd class="mt-0.5 font-medium {{ $reservation->status === \App\Enums\ReservationStatus::ReadyForPickup ? 'text-brand-700' : 'text-zinc-700' }}">
                                            {{ $reservation->pickup_deadline->format('d M Y, H:i') }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($reservation->status === \App\Enums\ReservationStatus::Pending)
                                <p class="mt-4 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">Waiting for library staff to approve this reservation.</p>
                            @elseif ($reservation->status === \App\Enums\ReservationStatus::ReadyForPickup)
                                <p class="mt-4 rounded-md bg-brand-50 px-3 py-2 text-xs text-brand-800">Ready for pickup. Please collect the book before the deadline.</p>
                            @elseif ($reservation->status === \App\Enums\ReservationStatus::Completed)
                                <p class="mt-4 rounded-md bg-emerald-50 px-3 py-2 text-xs text-emerald-800">This reservation has been completed.</p>
                            @elseif ($reservation->status === \App\Enums\ReservationStatus::Rejected)
                                <p class="mt-4 rounded-md bg-rose-50 px-3 py-2 text-xs text-rose-800">The library could not approve this reservation.</p>
                            @elseif ($reservation->status === \App\Enums\ReservationStatus::Expired)
                                <p class="mt-4 rounded-md bg-rose-50 px-3 py-2 text-xs text-rose-800">The pickup window has expired.</p>
                            @elseif ($reservation->status === \App\Enums\ReservationStatus::Cancelled)
                                <p class="mt-4 rounded-md bg-zinc-50 px-3 py-2 text-xs text-zinc-600">You cancelled this reservation.</p>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                @if (view()->exists('member.books.show'))
                                    <a href="{{ route('member.books.show', $reservation->book) }}" class="text-xs font-semibold text-brand-700 transition hover:text-brand-800">
                                        View book
                                    </a>
                                @endif

                                @if ($canCancel)
                                    <form method="POST" action="{{ route('member.reservations.cancel', $reservation) }}" data-confirm="Cancel this reservation?" class="sm:ml-auto">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">
                                            Cancel reservation
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <x-member.pagination :paginator="$reservations" />
    @endif
</x-layouts::member>
