@props(['paginator'])

@if ($paginator->count() > 0)
    <div class="flex flex-col gap-3 border-t border-zinc-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-zinc-500">
            Showing
            <span class="font-medium text-zinc-700">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-medium text-zinc-700">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium text-zinc-700">{{ $paginator->total() }}</span>
            results
        </p>

        @if ($paginator->hasPages())
            <nav class="flex items-center gap-4 text-xs font-medium" aria-label="Pagination">
                @if ($paginator->onFirstPage())
                    <span class="text-zinc-300">Previous</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="text-zinc-500 transition hover:text-zinc-900" rel="prev">Previous</a>
                @endif

                <div class="flex items-center gap-1">
                    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="flex size-8 items-center justify-center rounded-md bg-brand-600 text-white" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex size-8 items-center justify-center rounded-md text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="text-zinc-500 transition hover:text-zinc-900" rel="next">Next</a>
                @else
                    <span class="text-zinc-300">Next</span>
                @endif
            </nav>
        @endif
    </div>
@endif
