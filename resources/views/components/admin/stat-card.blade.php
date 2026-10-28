@props([
    'label',
    'value',
    'hint' => null,
])

<div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900">{{ $value }}</p>

    @if ($hint)
        <p class="mt-2 text-xs text-zinc-500">{{ $hint }}</p>
    @endif
</div>
