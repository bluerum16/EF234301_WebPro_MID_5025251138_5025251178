<x-layouts::admin title="Add librarian" subtitle="Create a staff account with librarian access to books, loans, and reservations.">
    <x-slot:actions>
        <a href="{{ route('admin.librarians.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20">
            Back to librarians
        </a>
    </x-slot:actions>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.librarians.store') }}" class="overflow-hidden rounded-lg border border-zinc-200/80 bg-white shadow-sm">
            @csrf

            <div class="border-b border-zinc-100 px-6 py-5">
                <h2 class="text-base font-semibold text-zinc-900">Account details</h2>
                <p class="mt-1 text-sm text-zinc-500">The librarian can sign in with these credentials as soon as the account is created.</p>
            </div>

            <div class="grid gap-5 px-6 py-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-zinc-700">Full name</label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="e.g. Siti Rahmawati"
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

                <div class="sm:col-span-2">
                    <label for="email" class="block text-sm font-medium text-zinc-700">Email address</label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="name@mybook.test"
                        @class([
                            'mt-1.5 block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:outline-none focus:ring-4',
                            'border-rose-300 focus:border-rose-400 focus:ring-rose-500/10' => $errors->has('email'),
                            'border-zinc-300 focus:border-brand-500 focus:ring-brand-500/10' => ! $errors->has('email'),
                        ])
                    />

                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="mb-4 h-px bg-zinc-100"></div>
                    <h3 class="text-sm font-semibold text-zinc-900">Set a password</h3>
                    <p class="mt-0.5 text-xs text-zinc-500">Choose a secure password and confirm it below.</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-700">Password</label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        @class([
                            'mt-1.5 block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:outline-none focus:ring-4',
                            'border-rose-300 focus:border-rose-400 focus:ring-rose-500/10' => $errors->has('password'),
                            'border-zinc-300 focus:border-brand-500 focus:ring-brand-500/10' => ! $errors->has('password'),
                        ])
                    />

                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-700">Confirm password</label>

                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/10"
                    />
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-zinc-100 bg-zinc-50/60 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('admin.librarians.index') }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                    Cancel
                </a>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
                    Create librarian
                </button>
            </div>
        </form>
    </div>
</x-layouts::admin>
