@props([
    'title' => 'Member area',
    'subtitle' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.member-head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    @php($homeRoute = view()->exists('member.books.index') ? route('member.books.index') : route('member.reservations.index'))

                    <a href="{{ $homeRoute }}" class="flex min-w-0 items-baseline gap-2">
                        <span class="truncate text-xl font-bold tracking-tight text-brand-700">
                            {{ config('app.name', 'myBook') }}
                        </span>
                        <span class="hidden text-[10px] font-medium uppercase tracking-wider text-zinc-400 sm:inline">
                            Member account
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
                    @if (view()->exists('member.books.index'))
                        @php($booksActive = request()->routeIs('member.books.*'))

                        <a href="{{ route('member.books.index') }}" @if ($booksActive) aria-current="page" @endif class="{{ $booksActive ? 'font-semibold text-brand-700' : 'text-zinc-500 transition hover:text-zinc-900' }}">
                            Catalog
                        </a>
                    @endif

                    @php($reservationsActive = request()->routeIs('member.reservations.*'))

                    <a href="{{ route('member.reservations.index') }}" @if ($reservationsActive) aria-current="page" @endif class="{{ $reservationsActive ? 'font-semibold text-brand-700' : 'text-zinc-500 transition hover:text-zinc-900' }}">
                        My reservations
                    </a>

                    @php($loansActive = request()->routeIs('member.loans.*'))

                    <a href="{{ route('member.loans.index') }}" @if ($loansActive) aria-current="page" @endif class="{{ $loansActive ? 'font-semibold text-brand-700' : 'text-zinc-500 transition hover:text-zinc-900' }}">
                        My borrowings
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ $title }}</h1>

                @if ($subtitle)
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500">{{ $subtitle }}</p>
                @endif
            </div>

            <x-member.flash />

            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 bg-white py-6">
            <div class="mx-auto w-full max-w-7xl px-4 text-xs text-zinc-500 sm:px-6 lg:px-8">
                {{ config('app.name', 'myBook') }} · Member account
            </div>
        </footer>

        <script>
            document.querySelectorAll('[data-dismiss]').forEach((button) => {
                button.addEventListener('click', () => button.closest('[data-flash]')?.remove());
            });

            document.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (!window.confirm(form.dataset.confirm)) {
                        event.preventDefault();
                    }
                });
            });
        </script>
    </body>
</html>
