@props(['book'])

@php
    $palette = [
        'bg-[#123b5d]',
        'bg-[#1f4d3a]',
        'bg-[#5a2a2a]',
        'bg-[#3c3a5e]',
        'bg-[#4a3b2a]',
        'bg-[#2b2b2b]',
    ];

    $cover = $palette[($book->categories_id ?? 0) % count($palette)];
@endphp

<div class="flex aspect-[3/4] w-16 shrink-0 flex-col justify-between rounded-md {{ $cover }} p-2 text-white sm:w-20">
    <p class="line-clamp-1 text-[7px] font-semibold uppercase tracking-wider text-white/60">
        {{ $book->category?->name ?? 'Book' }}
    </p>

    <p class="line-clamp-3 text-[9px] font-semibold leading-tight sm:text-[10px]">
        {{ $book->title }}
    </p>

    <p class="text-[7px] uppercase tracking-wider text-white/40">myBook</p>
</div>
