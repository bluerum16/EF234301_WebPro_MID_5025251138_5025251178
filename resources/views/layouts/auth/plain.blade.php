@props([
    'title' => 'Log in',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ filled($title) ? $title.' - '.config('app.name', 'myBook') : config('app.name', 'myBook') }}</title>

        <link rel="icon" href="/favicon.ico?v=2" sizes="any">
        <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">

        @fonts

        @vite(['resources/css/auth.css'])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="hidden bg-brand-700 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div>
                    <a href="{{ route('home') }}" class="text-2xl font-bold tracking-tight text-white">
                        {{ config('app.name', 'myBook') }}
                    </a>
                    <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-brand-200">
                        Library management
                    </p>
                </div>

                <div class="max-w-md">
                    <h2 class="text-3xl font-semibold leading-tight tracking-tight">
                        A simpler way to run the library.
                    </h2>
                    <p class="mt-4 text-sm leading-7 text-brand-100">
                        Manage the collection, reservations, and loans from one place. Built for library staff and members.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-brand-100">
                        <li>Catalog and stock management</li>
                        <li>Reservation and pickup workflow</li>
                        <li>Borrowing and return history</li>
                    </ul>
                </div>

                <p class="text-xs text-brand-200">
                    {{ config('app.name', 'myBook') }} · Library management system
                </p>
            </aside>

            <main class="flex items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <div class="mb-8 lg:hidden">
                        <a href="{{ route('home') }}" class="text-2xl font-bold tracking-tight text-brand-700">
                            {{ config('app.name', 'myBook') }}
                        </a>
                        <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-zinc-400">
                            Library management
                        </p>
                    </div>

                    {{ $slot }}

                    <p class="mt-8 text-center text-xs text-zinc-400">
                        © {{ date('Y') }} {{ config('app.name', 'myBook') }}
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
