<x-layouts::app :title="$title">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <flux:heading size="xl">{{ $title }}</flux:heading>
        <flux:text>{{ __('This page is not available yet.') }}</flux:text>
    </div>
</x-layouts::app>
