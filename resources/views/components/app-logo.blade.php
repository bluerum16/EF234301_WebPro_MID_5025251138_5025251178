@props([
    'sidebar' => false,
    'href' => null,
])

<a href="{{ $href ?? route('dashboard') }}" {{ $attributes->class('flex h-10 items-center') }}>
    <span class="truncate text-base font-bold tracking-tight text-brand-700 dark:text-white">
        {{ config('app.name', 'myBook') }}
    </span>
</a>
