@props([
    'book',
    'size' => 'card',
])

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
    $large = $size === 'large';
@endphp

<div class="flex aspect-[3/4] flex-col justify-between {{ $large ? 'p-7 sm:p-8' : 'p-5' }} {{ $cover }} text-white">
    <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">
        {{ $book->category?->name ?? 'Uncategorized' }}
    </p>

    <div class="min-h-0">
        <h3 class="line-clamp-3 {{ $large ? 'text-2xl sm:text-3xl' : 'text-lg' }} font-semibold leading-snug">
            {{ $book->title }}
        </h3>
        <p class="mt-2 line-clamp-1 {{ $large ? 'text-base' : 'text-sm' }} text-white/70">
            {{ $book->author }}
        </p>
    </div>

    <p class="text-[10px] uppercase tracking-wider text-white/40">myBook collection</p>
</div>
