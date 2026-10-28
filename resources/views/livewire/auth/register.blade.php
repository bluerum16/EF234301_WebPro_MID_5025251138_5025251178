<x-layouts::auth.plain :title="__('Register')">
    <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Create an account</h1>
            <p class="mt-1 text-sm text-zinc-500">Join {{ config('app.name', 'myBook') }} to reserve books and track your borrowings.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-zinc-700">Full name</label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Your full name"
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
                <label for="email" class="block text-sm font-medium text-zinc-700">Email address</label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
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

                <p class="mt-1.5 text-xs text-zinc-500">Use a secure password and confirm it below.</p>

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

            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30" data-test="register-user-button">
                Create account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-brand-700 transition hover:text-brand-800">
                Log in
            </a>
        </p>
    </div>
</x-layouts::auth.plain>
