<x-layouts::admin title="Add category" subtitle="Create a category that groups related books in the catalog.">
    <x-slot:actions>
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
            Back to categories
        </a>
    </x-slot:actions>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
            @csrf

            <div class="border-b border-zinc-100 px-6 py-5">
                <h2 class="text-base font-semibold text-zinc-900">Category details</h2>
                <p class="mt-1 text-sm text-zinc-500">Give the category a clear name and a short description for members.</p>
            </div>

            <div class="space-y-5 px-6 py-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700">Category name</label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        placeholder="e.g. Science fiction"
                        @class([
                            'mt-1.5 block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:outline-none focus:ring-4',
                            'border-rose-300 focus:border-rose-400 focus:ring-rose-500/10' => $errors->has('name'),
                            'border-zinc-300 focus:border-brand-500 focus:ring-brand-500/10' => ! $errors->has('name'),
                        ])
                    />

                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label for="description" class="block text-sm font-medium text-zinc-700">Description</label>
                        <span class="text-[11px] text-zinc-400">Max. 500 characters</span>
                    </div>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        required
                        placeholder="Describe the kinds of books in this category..."
                        @class([
                            'mt-1.5 block w-full resize-y rounded-lg border bg-white px-3.5 py-2.5 text-sm leading-6 text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:outline-none focus:ring-4',
                            'border-rose-300 focus:border-rose-400 focus:ring-rose-500/10' => $errors->has('description'),
                            'border-zinc-300 focus:border-brand-500 focus:ring-brand-500/10' => ! $errors->has('description'),
                        ])
                    >{{ old('description') }}</textarea>

                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-zinc-100 bg-zinc-50/60 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                    Cancel
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
                    Create category
                </button>
            </div>
        </form>
    </div>
</x-layouts::admin>
