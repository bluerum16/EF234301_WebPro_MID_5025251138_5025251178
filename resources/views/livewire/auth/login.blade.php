<x-layouts::auth.plain :title="__('Log in')">
    <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Log in</h1>
            <p class="mt-1 text-sm text-zinc-500">Use your {{ config('app.name', 'myBook') }} account to continue.</p>
        </div>

        @if (session('status'))
            <div class="mt-5 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-700">Email address</label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
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
                <div class="flex items-center justify-between gap-3">
                    <label for="password" class="block text-sm font-medium text-zinc-700">Password</label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-700 transition hover:text-brand-800">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
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

            <label class="flex items-center gap-2 text-sm text-zinc-600">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                    class="size-4 rounded border-zinc-300 accent-brand-600"
                />

                Remember me
            </label>

            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30" data-test="login-button">
                Log in
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-brand-700 transition hover:text-brand-800">
                Register
            </a>
        </p>
    </div>
</x-layouts::auth.plain>
