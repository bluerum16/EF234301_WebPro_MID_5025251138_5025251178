<x-layouts::librarian title="Reservation Management" subtitle="Review online member reservations, approve pickup requests, and confirm collections.">
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-admin.stat-card
            label="Pending Approvals"
            :value="$pendingCount"
            hint="Online reservation requests waiting for librarian action"
        />

        <x-admin.stat-card
            label="Ready for Pickup"
            :value="$readyCount"
            hint="Approved books waiting for members to collect at desk"
        />
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-1.5">
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'pending')"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'pending' ? 'bg-brand-50
                    text-brand-700 ring-1 ring-inset ring-brand-200/70' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                >
                    Pending
                    @if ($pendingCount > 0)
                        <span class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">{{ $pendingCount }}</span>
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="$set('statusFilter', 'ready_for_pickup')"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'ready_for_pickup' ? 'bg-brand-50
                    text-brand-700 ring-1 ring-inset ring-brand-200/70' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                >
                    Ready for Pickup
                    @if ($readyCount > 0)
                        <span class="rounded-full bg-sky-100 px-1.5 py-0.5 text-[10px] font-bold text-sky-800">{{ $readyCount }}</span>
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="$set('statusFilter', 'all')"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'all' ? 'bg-brand-50 text-brand-700 ring-1 ring-inset
                    ring-brand-200/70' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                >
                    All Reservations
                </button>
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

        @if ($reservations->isEmpty())
            <x-admin.empty-state
                title="{{ $search ? 'No reservations match your search' : 'No reservations found' }}"
                description="{{ $search ? 'Try searching for a different member name or book title.' : 'There are currently no reservations in this category.' }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Member</th>
                            <th scope="col" class="px-5 py-3">Book</th>
                            <th scope="col" class="px-5 py-3">Pickup Deadline</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($reservations as $reservation)
                            <tr class="transition hover:bg-zinc-50/60">
                                <!-- Member Info -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-admin.avatar :initials="$reservation->member->initials()" size="sm" tone="zinc" />
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-zinc-900">{{ $reservation->member->name }}</p>
                                            <p class="truncate text-xs text-zinc-500">{{ $reservation->member->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="min-w-0 max-w-xs">
                                        <p class="truncate font-medium text-zinc-900">{{ $reservation->book->title }}</p>
                                        <p class="truncate text-xs text-zinc-500">
                                            {{ $reservation->book->author }} ·
                                            <span class="text-zinc-400">Available: {{ $reservation->book->availableStock() }}</span>
                                        </p>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-xs text-zinc-600">
                                    @if ($reservation->pickup_deadline)
                                        <p class="font-medium text-zinc-900">{{ $reservation->pickup_deadline->format('d M Y, H:i') }}</p>
                                        <p class="{{ $reservation->pickup_deadline->isPast() ? 'text-rose-600 font-semibold' : 'text-zinc-400' }}">
                                            {{ $reservation->pickup_deadline->diffForHumans() }}
                                        </p>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $tone = match ($reservation->status) {
                                            \App\Enums\ReservationStatus::Pending => 'amber',
                                            \App\Enums\ReservationStatus::ReadyForPickup => 'sky',
                                            \App\Enums\ReservationStatus::Completed => 'emerald',
                                            \App\Enums\ReservationStatus::Rejected => 'rose',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <x-admin.badge :tone="$tone">
                                        {{ $reservation->status->label() }}
                                    </x-admin.badge>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if ($reservation->status === \App\Enums\ReservationStatus::Pending)
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                wire:click="approve({{ $reservation->id }})"
                                                wire:confirm="Approve this reservation? The member will be given 2 days to pick up the book."
                                                class="inline-flex items-center rounded-lg bg-brand-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition
                                                hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                            >
                                                Approve
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="reject({{ $reservation->id }})"
                                                wire:confirm="Reject this reservation request?"
                                                class="inline-flex items-center rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-rose-600
                                                ￹shadow-sm transition hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                    @elseif ($reservation->status === \App\Enums\ReservationStatus::ReadyForPickup)
                                        <button
                                            type="button"
                                            wire:click="confirmPickup({{ $reservation->id }})"
                                            wire:confirm="Confirm pickup for {{ $reservation->member->name }}? This will mark the reservation as completed and create a new
                                            ￹loan record."
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition
                                            ￹hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                        >
                                            Confirm pickup
                                        </button>
                                    @else
                                        <span class="text-xs text-zinc-400">Processed</span>
                                    @endif
                                </td>
                </tr>
                        @endforeach
            </tbody>
                </table>
            </div>

            @if ($reservations->hasPages())
                <div class="border-t border-zinc-100 px-5 py-3">
            {{ $reservations->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts::librarian>