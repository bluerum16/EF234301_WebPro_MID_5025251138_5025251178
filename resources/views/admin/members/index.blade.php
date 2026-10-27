@php
    $currentStatus = in_array($status, ['active', 'inactive'], true) ? $status : 'all';
    $tabs = ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'];
@endphp

<x-layouts::admin title="Members" subtitle="Review the member list and control whether an account can access the library.">
    <div class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">Member accounts</h2>
                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $members->total() }} {{ \Illuminate\Support\Str::plural('member', $members->total()) }} found
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="inline-flex shrink-0 rounded-lg bg-zinc-100 p-1">
                    @foreach ($tabs as $key => $label)
                        @php($activeTab = $currentStatus === $key)

                        <a
                            href="{{ route('admin.members.index', array_filter(['search' => $search, 'status' => $key === 'all' ? null : $key])) }}"
                            @if ($activeTab) aria-current="page" @endif
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $activeTab ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-800' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.members.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    @if ($currentStatus !== 'all')
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                    @endif

                    <div class="relative">
                        <input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by name or email"
                            class="w-full rounded-lg border border-zinc-300 bg-white py-2.5 pl-3 pr-3 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 sm:w-64"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                            Search
                        </button>

                        @if ($search)
                            <a href="{{ route('admin.members.index', array_filter(['status' => $currentStatus === 'all' ? null : $currentStatus])) }}" class="inline-flex items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium text-zinc-500 transition hover:text-zinc-800">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        @if ($members->isEmpty())
            <x-admin.empty-state
                title="{{ $search ? 'No members match your search' : 'No member accounts found' }}"
                description="{{ $search ? 'Try a different name, email address, or account status.' : 'Members appear here after they register through the website.' }}"
            >
                @if ($search || $currentStatus !== 'all')
                    <a href="{{ route('admin.members.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                        Clear filters
                    </a>
                @endif
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Member</th>
                            <th scope="col" class="px-5 py-3">Joined</th>
                            <th scope="col" class="px-5 py-3">Activity</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($members as $member)
                            <tr class="transition hover:bg-zinc-50/60">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-admin.avatar :initials="$member->initials()" tone="{{ $member->is_active ? 'sky' : 'zinc' }}" />

                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-zinc-900">{{ $member->name }}</p>
                                            <p class="truncate text-xs text-zinc-500">{{ $member->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500">
                                    {{ $member->created_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                                        <span><span class="font-semibold text-zinc-800">{{ $member->loans_count }}</span> loans</span>
                                        
                                        <span><span class="font-semibold text-zinc-800">{{ $member->reservations_count }}</span> reservations</span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($member->is_active)
                                        <x-admin.badge tone="emerald">Active</x-admin.badge>
                                    @else
                                        <x-admin.badge tone="rose">Inactive</x-admin.badge>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        <form
                                            method="POST"
                                            action="{{ route('admin.members.toggle', $member) }}"
                                            data-confirm="{{ $member->is_active ? 'Deactivate' : 'Activate' }} this member account?"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            @if ($member->is_active)
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700" aria-label="Deactivate {{ $member->name }}">
                                                    Deactivate
                                                </button>
                                            @else
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-700" aria-label="Activate {{ $member->name }}">
                                                    Activate
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin.pagination :paginator="$members" />
        @endif
    </div>
</x-layouts::admin>
