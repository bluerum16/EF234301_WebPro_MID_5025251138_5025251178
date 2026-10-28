<x-layouts::admin title="Categories" subtitle="Organize the collection into categories that members can browse and filter.">
    <x-slot:actions>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
            Add category
        </a>
    </x-slot:actions>

    <div class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">Book categories</h2>
                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $categories->total() }} {{ \Illuminate\Support\Str::plural('category', $categories->total()) }} found
                </p>
            </div>

            <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search categories"
                        class="w-full rounded-lg border border-zinc-300 bg-white py-2.5 pl-3 pr-3 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 sm:w-64"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
                        Search
                    </button>

                    @if ($search)
                        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium text-zinc-500 transition hover:text-zinc-800">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if ($categories->isEmpty())
            <x-admin.empty-state
                title="{{ $search ? 'No categories match your search' : 'No categories yet' }}"
                description="{{ $search ? 'Try a different category name.' : 'Add categories to help members browse and filter the book catalog.' }}"
            >
                @if ($search)
                    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                        Clear search
                    </a>
                @else
                    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Add category
                    </a>
                @endif
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Category</th>
                            <th scope="col" class="px-5 py-3">Books</th>
                            <th scope="col" class="px-5 py-3">Created</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($categories as $category)
                            <tr class="transition hover:bg-zinc-50/60">
                                <td class="max-w-md px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        

                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-zinc-900">{{ $category->name }}</p>
                                            <p class="mt-0.5 line-clamp-1 text-xs text-zinc-500">{{ $category->description }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                                        {{ $category->books_count }} {{ \Illuminate\Support\Str::plural('book', $category->books_count) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-zinc-500">
                                    {{ $category->created_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900" aria-label="Edit {{ $category->name }}">
                                            Edit
                                        </a>

                                        @if ($category->books_count > 0)
                                            <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-zinc-300" title="Categories that still contain books cannot be deleted">
                                                Delete
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete this category? This action cannot be undone.">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 hover:text-rose-700" aria-label="Delete {{ $category->name }}">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin.pagination :paginator="$categories" />
        @endif
    </div>
</x-layouts::admin>
