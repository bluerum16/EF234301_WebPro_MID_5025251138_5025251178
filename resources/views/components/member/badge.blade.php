@props(['tone' => 'zinc'])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-200/70',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-200/70',
        'zinc' => 'bg-zinc-100 text-zinc-600 ring-zinc-200',
    ];
@endphp

<span class="inline-flex items-center rounded-md px-2 py-1 text-[11px] font-medium ring-1 ring-inset {{ $tones[$tone] ?? $tones['zinc'] }}">
    {{ $slot }}
</span>
