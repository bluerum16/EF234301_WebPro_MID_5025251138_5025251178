@props([
    'title' => 'Book catalog',
    'subtitle' => null,
    'showHeader' => true,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.catalog-head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <a href="{{ route('member.books.index') }}" class="flex min-w-0 items-baseline gap-2">
                        <span class="truncate text-xl font-bold tracking-tight text-brand-700">
                            {{ config('app.name', 'myBook') }}
                        </span>
                        <span class="hidden text-[10px] font-medium uppercase tracking-wider text-zinc-400 sm:inline">
                            Library catalog
                        </span>
                    </a>

                    <div class="flex items-center gap-3 text-sm">
                        <span class="hidden max-w-48 truncate text-zinc-500 sm:inline">{{ auth()->user()->name }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" class="text-zinc-500 transition hover:text-zinc-900">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>

                <nav class="flex items-center gap-5 border-t border-zinc-100 py-3 text-sm" aria-label="Member navigation">
                    @php($catalogActive = request()->routeIs('member.books.*'))

                    <a href="{{ route('member.books.index') }}" @if ($catalogActive) aria-current="page" @endif class="{{ $catalogActive ? 'font-semibold text-brand-700' : 'text-zinc-500 transition hover:text-zinc-900' }}">
                        Catalog
                    </a>

                    @if (view()->exists('member.reservations.index'))
                        <a href="{{ route('member.reservations.index') }}" class="text-zinc-500 transition hover:text-zinc-900">
                            My reservations
                        </a>
                    @endif

                    @if (view()->exists('member.loans.index'))
                        <a href="{{ route('member.loans.index') }}" class="text-zinc-500 transition hover:text-zinc-900">
                            My borrowings
                        </a>
                    @endif
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if ($showHeader)
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ $title }}</h1>

                    @if ($subtitle)
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 bg-white py-6">
            <div class="mx-auto w-full max-w-7xl px-4 text-xs text-zinc-500 sm:px-6 lg:px-8">
                {{ config('app.name', 'myBook') }} · Library collection
            </div>
        </footer>
    </body>
</html>
