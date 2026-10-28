<x-layouts::librarian title="Walk-In Borrowing" subtitle="Record an on-the-spot physical book borrowing for an active library member.">
    <x-slot:actions>
        <a href="{{ route('librarian.loans.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium
        text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
            Back to loans
        </a>
    </x-slot:actions>

    <div class="max-w-3xl">
        <form wire:submit="save" class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
            <div class="border-b border-zinc-100 px-6 py-5">
                <h2 class="text-base font-semibold text-zinc-900">Borrowing details</h2>
                <p class="mt-1 text-sm text-zinc-500">Select the active member and the book being borrowed. The standard loan duration is 14 days.</p>
            </div>

            <div class="grid gap-5 px-6 py-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="users_id" class="block text-sm font-medium text-zinc-700">Library Member</label>

                    <select
                        id="users_id"
                        wire:model="users_id"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500
                        focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                    >
                        <option value="">-- Select active member --</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }} ({{ $member->email }})
                            </option>
                        @endforeach
                    </select>

                    @error('users_id')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="books_id" class="block text-sm font-medium text-zinc-700">Book to Borrow</label>

                    <select
                        id="books_id"
                        wire:model="books_id"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500
                        focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                    >
                        <option value="">-- Select book with available stock --</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">
                                {{ $book->title }} — by {{ $book->author }} (Available: {{ $book->availableStock() }} of {{ $book->total_stock }})
                            </option>
                        @endforeach
                    </select>

                    @error('books_id')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="borrow_date" class="block text-sm font-medium text-zinc-700">Borrow Date</label>

                    <input
                        id="borrow_date"
                        type="date"
                        wire:model="borrow_date"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500
                        focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                    />

                    @error('borrow_date')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
            <label for="due_date" class="block text-sm font-medium text-zinc-700">Due Date (Return Deadline)</label>

            <input
                        id="due_date"
                        type="date"
                        wire:model="due_date"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition focus:border-brand-500
                        ￹focus:outline-none focus:ring-4 focus:ring-brand-500/10"
            />

            @error('due_date')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50/60 px-6 py-4">
                <a
            href="{{ route('librarian.loans.index') }}"
            class="rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                >
            Cancel
                </a>

                <button
            type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700
                    ￹focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30"
                >
            <span wire:loading.remove wire:target="save">Record borrowing</span>
            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                        <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                        Saving...
            </span>
                </button>
            </div>
        </form>
    </div>
</x-layouts::librarian>