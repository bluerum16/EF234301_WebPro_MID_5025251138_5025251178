@if (session('success') || session('error'))
    <div class="mb-5 space-y-3">
        @if (session('success'))
            <div data-flash role="status" class="flex items-start justify-between gap-4 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-emerald-900">Success</p>
                    <p class="mt-0.5 text-sm text-emerald-800">{{ session('success') }}</p>
                </div>

                <button type="button" data-dismiss class="shrink-0 text-xs font-medium text-emerald-700 transition hover:text-emerald-900">
                    Dismiss
                </button>
            </div>
        @endif

        @if (session('error'))
            <div data-flash role="alert" class="flex items-start justify-between gap-4 rounded-lg border-l-4 border-rose-500 bg-rose-50 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-rose-900">Action failed</p>
                    <p class="mt-0.5 text-sm text-rose-800">{{ session('error') }}</p>
                </div>

                <button type="button" data-dismiss class="shrink-0 text-xs font-medium text-rose-700 transition hover:text-rose-900">
                    Dismiss
                </button>
            </div>
        @endif
    </div>
@endif
