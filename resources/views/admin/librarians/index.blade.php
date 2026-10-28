<x-layouts::admin title="Librarians" subtitle="Create and maintain the staff accounts that run day-to-day library operations.">
    <x-slot:actions>
        <a href="{{ route('admin.librarians.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
            Add librarian
        </a>
    </x-slot:actions>

    <div class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">Librarian accounts</h2>
                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $librarians->total() }} {{ \Illuminate\Support\Str::plural('account', $librarians->total()) }} found
                </p>
            </div>

            <form method="GET" action="{{ route('admin.librarians.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
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
                        <a href="{{ route('admin.librarians.index') }}" class="inline-flex items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium text-zinc-500 transition hover:text-zinc-800">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if ($librarians->isEmpty())
            <x-admin.empty-state
                title="{{ $search ? 'No librarians match your search' : 'No librarian accounts yet' }}"
                description="{{ $search ? 'Try a different name or email address.' : 'Create the first librarian account so staff can manage books, loans, and reservations.' }}"
            >
                @if ($search)
                    <a href="{{ route('admin.librarians.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                        Clear search
                    </a>
                @else
                    <a href="{{ route('admin.librarians.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Add librarian
                    </a>
                @endif
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Librarian</th>
                            <th scope="col" class="px-5 py-3">Joined</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($librarians as $librarian)
                            <tr class="transition hover:bg-zinc-50/60">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-admin.avatar :initials="$librarian->initials()" tone="brand" />

                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-zinc-900">{{ $librarian->name }}</p>
                                            <p class="truncate text-xs text-zinc-500">{{ $librarian->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500">
                                    {{ $librarian->created_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($librarian->is_active)
                                        <x-admin.badge tone="emerald">Active</x-admin.badge>
                                    @else
                                        <x-admin.badge tone="rose">Inactive</x-admin.badge>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.librarians.edit', $librarian) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900" aria-label="Edit {{ $librarian->name }}">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.librarians.destroy', $librarian) }}" data-confirm="Delete this librarian account? This action cannot be undone.">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700" aria-label="Delete {{ $librarian->name }}">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin.pagination :paginator="$librarians" />
        @endif
    </div>
</x-layouts::admin>
