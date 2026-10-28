@props([
    'title',
    'description' => null,
])

<div class="rounded-lg border border-zinc-200 bg-white px-6 py-16 text-center shadow-sm">
    <h3 class="text-sm font-semibold text-zinc-900">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-zinc-500">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
