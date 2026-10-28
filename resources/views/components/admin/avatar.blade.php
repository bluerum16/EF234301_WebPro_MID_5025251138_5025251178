@props([
    'initials' => '?',
    'tone' => 'brand',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'size-8 text-[11px]',
        'md' => 'size-10 text-xs',
        'lg' => 'size-12 text-sm',
    ];

    $tones = [
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-100',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'zinc' => 'bg-zinc-100 text-zinc-600 ring-zinc-200',
    ];
@endphp

<span
    class="inline-flex shrink-0 items-center justify-center rounded-full font-semibold uppercase ring-1 ring-inset {{ $sizes[$size] ?? $sizes['md'] }} {{ $tones[$tone] ?? $tones['brand'] }}"
    aria-hidden="true"
>
    {{ filled($initials) ? $initials : '?' }}
</span>
