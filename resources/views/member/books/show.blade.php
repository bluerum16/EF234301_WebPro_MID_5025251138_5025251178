<x-layouts::catalog :title="$book->title" :show-header="false">
    <nav class="mb-6 flex items-center gap-1.5 text-sm text-zinc-500" aria-label="Breadcrumb">
        <a href="{{ route('member.books.index') }}" class="transition hover:text-brand-700">Catalog</a>
        <span class="text-zinc-300">/</span>
        <span class="text-zinc-700">{{ $book->category?->name ?? 'Book details' }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        <div class="mx-auto w-full max-w-[300px] overflow-hidden rounded-lg border border-zinc-200 shadow-sm lg:mx-0">
            <x-catalog.book-cover :book="$book" size="large" />
        </div>

        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-700">
                {{ $book->category?->name ?? 'Uncategorized' }}
            </p>

            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">{{ $book->title }}</h1>
            <p class="mt-1 text-lg text-zinc-600">{{ $book->author }}</p>

            <dl class="mt-6 grid grid-cols-2 gap-5 border-y border-zinc-200 py-5 text-sm">
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">ISBN</dt>
                    <dd class="mt-1 font-medium text-zinc-900">{{ $book->isbn }}</dd>
                </div>

                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Availability</dt>
                    <dd class="mt-1 font-medium {{ $availableStock > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                        {{ $availableStock > 0 ? $availableStock.' '.\Illuminate\Support\Str::plural('copy', $availableStock).' available' : 'Currently unavailable' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Category</dt>
                    <dd class="mt-1 font-medium text-zinc-900">{{ $book->category?->name ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Total stock</dt>
                    <dd class="mt-1 font-medium text-zinc-900">{{ $book->total_stock }} {{ \Illuminate\Support\Str::plural('copy', $book->total_stock) }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <h2 class="text-sm font-semibold text-zinc-900">About this book</h2>
                <p class="mt-2 text-sm leading-7 text-zinc-600">{{ $book->description }}</p>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                @if (Route::has('member.reservations.store'))
                    @if ($availableStock > 0)
                        <form method="POST" action="{{ route('member.reservations.store') }}">
                            @csrf
                            <input type="hidden" name="books_id" value="{{ $book->id }}">

                            <button type="submit" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
                                Reserve this book
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center rounded-lg bg-zinc-100 px-5 py-2.5 text-sm font-medium text-zinc-500">
                            Currently unavailable
                        </span>
                    @endif
                @else
                    <span class="inline-flex items-center rounded-lg bg-zinc-100 px-5 py-2.5 text-sm font-medium text-zinc-500">
                        Online reservation coming soon
                    </span>
                @endif

                <a href="{{ route('member.books.index') }}" class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                    Back to catalog
                </a>
            </div>

            @if (Route::has('member.reservations.store'))
                <p class="mt-3 text-xs leading-5 text-zinc-500">
                    Reservations are confirmed by library staff. Once approved, collect the book within the pickup window.
                </p>
            @endif
        </div>
    </div>
</x-layouts::catalog>
