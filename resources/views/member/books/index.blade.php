@php
    $activeCategory = $selectedCategory ? $categories->firstWhere('id', (int) $selectedCategory) : null;
@endphp

<x-layouts::catalog title="Book catalog" subtitle="Browse the library collection, search by title or author, and check which books are available.">
    <form method="GET" action="{{ route('member.books.index') }}" class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="min-w-0 flex-1">
                <label for="search" class="sr-only">Search books</label>

                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by title or author"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                />
            </div>

            @if ($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif

            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
                    Search
                </button>

                @if ($search || $selectedCategory)
                    <a href="{{ route('member.books.index') }}" class="inline-flex items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium text-zinc-500 transition hover:text-zinc-900">
                        Clear
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4">
            <span class="mr-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Categories</span>

            <a
                href="{{ route('member.books.index', array_filter(['search' => $search])) }}"
                @if (! $selectedCategory) aria-current="page" @endif
                class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ ! $selectedCategory ? 'bg-brand-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 hover:text-zinc-900' }}"
            >
                All
            </a>

            @foreach ($categories as $category)
                <a
                    href="{{ route('member.books.index', array_filter(['search' => $search, 'category' => $category->id])) }}"
                    @if ((string) $selectedCategory === (string) $category->id) aria-current="page" @endif
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition {{ (string) $selectedCategory === (string) $category->id ? 'bg-brand-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 hover:text-zinc-900' }}"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </form>

    <div class="mt-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-zinc-900">
                {{ $books->total() }} {{ \Illuminate\Support\Str::plural('book', $books->total()) }}
            </h2>

            <p class="mt-0.5 text-sm text-zinc-500">
                @if ($search)
                    Results for &ldquo;{{ $search }}&rdquo;
                @elseif ($activeCategory)
                    Books in {{ $activeCategory->name }}
                @else
                    Books in the library collection
                @endif
            </p>
        </div>

        @if ($books->hasPages())
            <p class="text-xs text-zinc-400">Page {{ $books->currentPage() }} of {{ $books->lastPage() }}</p>
        @endif
    </div>

    @if ($books->isEmpty())
        <div class="mt-4 rounded-lg border border-zinc-200 bg-white px-6 py-16 text-center shadow-sm">
            <h3 class="text-sm font-semibold text-zinc-900">
                {{ $search || $selectedCategory ? 'No books match your filters' : 'No books in the catalog yet' }}
            </h3>

            <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-zinc-500">
                {{ $search || $selectedCategory ? 'Try a different title, author, or category.' : 'The library has not added any books yet. Please check back later.' }}
            </p>

            @if ($search || $selectedCategory)
                <a href="{{ route('member.books.index') }}" class="mt-5 inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Clear filters
                </a>
            @endif
        </div>
    @else
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($books as $book)
                <x-catalog.book-card :book="$book" />
            @endforeach
        </div>

        <x-catalog.pagination :paginator="$books" />
    @endif
</x-layouts::catalog>
