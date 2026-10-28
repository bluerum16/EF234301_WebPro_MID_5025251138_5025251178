@props(['book'])

@php($available = $book->availableStock())

<a href="{{ route('member.books.show', $book) }}" class="group flex flex-col overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition hover:border-brand-300">
    <x-catalog.book-cover :book="$book" />

    <div class="flex items-center justify-between gap-3 border-t border-zinc-100 px-4 py-3">
        <span class="text-xs font-medium {{ $available > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
            {{ $available > 0 ? $available.' '.\Illuminate\Support\Str::plural('copy', $available).' available' : 'Unavailable' }}
        </span>

        <span class="text-xs font-semibold text-brand-700 transition group-hover:text-brand-800">
            View details
        </span>
    </div>
</a>
