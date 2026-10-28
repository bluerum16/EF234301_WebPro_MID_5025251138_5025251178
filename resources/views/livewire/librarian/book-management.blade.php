<x-layouts::librarian title="Book Management" subtitle="View, add, edit, and manage the library's book catalog and stock.">
    <x-slot:actions>
        <button
            type="button"
            wire:click="create"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700
            focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30"
        >
            + Add book
        </button>
    </x-slot:actions>

    <div class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-base font-semibold text-zinc-900">Book Catalog</h2>
                <p class="mt-0.5 text-sm text-zinc-500">
                    {{ $books->total() }} {{ \Illuminate\Support\Str::plural('book', $books->total()) }} found
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <select
                    wire:model.live="selectedCategory"
                    class="rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500 focus:outline-none
                    focus:ring-4 focus:ring-brand-500/10"
                >
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <div class="relative flex items-center gap-2">
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search title, author, or ISBN..."
                        class="w-full rounded-lg border border-zinc-300 bg-white py-2.5 pl-3 pr-8 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400
                        focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10 sm:w-72"
                    />
                    <div wire:loading wire:target="search" class="absolute right-2.5 top-3 text-zinc-400">
                        <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-zinc-300 border-t-brand-600"></span>
                    </div>
                </div>
            </div>
        </div>

        @if ($books->isEmpty())
            <x-admin.empty-state
                title="{{ $search ? 'No books match your search' : ($selectedCategory ? 'No books in this category' : 'No books yet') }}"
                description="{{ $search ? 'Try a different title, author, or ISBN.' : 'Start building the catalog by adding the first book.' }}"
            >
                @unless ($search || $selectedCategory)
                    <button
                        type="button"
                        wire:click="create"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                    >
                        Add first book
                    </button>
                @endunless
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Book</th>
                            <th scope="col" class="px-5 py-3">Category</th>
                            <th scope="col" class="px-5 py-3">ISBN</th>
                            <th scope="col" class="px-5 py-3">Stock</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($books as $book)
                            <tr class="transition hover:bg-zinc-50/60">
                                <td class="px-5 py-4">
                                    <div class="min-w-0 max-w-xs">
                                        <p class="truncate font-medium text-zinc-900">{{ $book->title }}</p>
                                        <p class="truncate text-xs text-zinc-500">{{ $book->author }}</p>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <x-admin.badge tone="brand">{{ $book->category->name }}</x-admin.badge>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-zinc-500">
                                    {{ $book->isbn }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php($available = $book->availableStock())
                                    <div class="text-xs">
                                        <span class="font-semibold {{ $available === 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            {{ $available }}
                                        </span>
                                        <span class="text-zinc-400">/ {{ $book->total_stock }} available</span>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            wire:click="edit({{ $book->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100
                                            hover:text-zinc-900"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="delete({{ $book->id }})"
                                            wire:confirm="Delete '{{ $book->title }}'? This action cannot be undone."
                                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50
                                            hover:text-rose-700"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($books->hasPages())
                <div class="border-t border-zinc-100 px-5 py-3">
                    {{ $books->links() }}
                </div>
            @endif
        @endif
    </div>

    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
        >
            <div
                wire:click="closeModal"
                class="absolute inset-0 bg-zinc-950/50 backdrop-blur-sm"
            ></div>

            <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
                    <div>
                        <h2 id="modal-title" class="text-base font-semibold text-zinc-900">
                            {{ $editingBookId ? 'Edit Book' : 'Add New Book' }}
                        </h2>
                        <p class="mt-0.5 text-sm text-zinc-500">
                            {{ $editingBookId ? 'Update the book details below.' : 'Fill in the details to add a new book to the catalog.' }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700"
                        aria-label="Close modal"
                    >
                        <span class="block text-lg leading-none">&times;</span>
                    </button>
                </div>

                <form wire:submit="save" class="overflow-y-auto px-6 py-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-zinc-700">Title</label>

                            <input
                                id="title"
                                type="text"
                                wire:model="title"
                                placeholder="e.g. Laskar Pelangi"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition
                                placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                            />

                            @error('title')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="author" class="block text-sm font-medium text-zinc-700">Author</label>

                            <input
                                id="author"
                                type="text"
                                wire:model="author"
                                placeholder="e.g. Andrea Hirata"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition
                                placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                            />

                            @error('author')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="isbn" class="block text-sm font-medium text-zinc-700">ISBN</label>

                            <input
                                id="isbn"
                                type="text"
                                wire:model="isbn"
                                placeholder="e.g. 9786020612041"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition font-mono
                                placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                            />

                            @error('isbn')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="categories_id" class="block text-sm font-medium text-zinc-700">Category</label>

                            <select
                                id="categories_id"
                                wire:model="categories_id"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition
                                focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                            >
                                <option value="">-- Select category --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>

                            @error('categories_id')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="total_stock" class="block text-sm font-medium text-zinc-700">Total Stock (Physical Copies)</label>

                            <input
                                id="total_stock"
                                type="number"
                                min="1"
                                wire:model="total_stock"
                                class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition
                                focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                            />

                            @error('total_stock')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-zinc-700">
                                Description
                                <span class="text-zinc-400 font-normal">(optional)</span>
                            </label>

                            <textarea
                                id="description"
                                wire:model="description"
                                rows="3"
                                placeholder="Brief synopsis or notes about the book..."
                                class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition
                                ￹placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                ></textarea>

                @error('description')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
                        </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-zinc-100 pt-5">
                        <button
                type="button"
                wire:click="closeModal"
                class="rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                        >
                Cancel
                        </button>

                        <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700
                            ￹focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30"
                        >
                <span wire:loading.remove wire:target="save">
                                {{ $editingBookId ? 'Save changes' : 'Add book' }}
                </span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                                Saving...
                </span>
                        </button>
            </div>
                </form>
            </div>
        </div>
    @endif
</x-layouts::librarian>