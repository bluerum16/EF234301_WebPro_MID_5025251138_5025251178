@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.admin-head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <a href="#admin-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-zinc-900 focus:shadow-lg">
            Skip to content
        </a>

        <div class="min-h-screen lg:flex">
            <div id="admin-overlay" class="fixed inset-0 z-40 hidden bg-zinc-950/40 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

            <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-zinc-200 bg-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:translate-x-0 print:hidden">
                <div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-zinc-100 px-5">
                    <a href="{{ route('admin.dashboard') }}" class="min-w-0 leading-tight">
                        <p class="truncate text-lg font-bold tracking-tight text-brand-700">{{ config('app.name', 'myBook') }}</p>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-zinc-400">Library admin</p>
                    </a>

                    <button type="button" id="sidebar-close" class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 lg:hidden" aria-label="Close navigation">
                        <span class="block text-xl leading-none">&times;</span>
                    </button>
                </div>

                <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Admin navigation">
                    @php
                        $navGroups = [
                            'Overview' => [
                                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                            ],
                            'Management' => [
                                ['label' => 'Librarians', 'route' => 'admin.librarians.index', 'active' => 'admin.librarians.*'],
                                ['label' => 'Members', 'route' => 'admin.members.index', 'active' => 'admin.members.*'],
                                ['label' => 'Categories', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                            ],
                            'Insights' => [
                                ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*'],
                            ],
                        ];
                    @endphp

                    @foreach ($navGroups as $group => $items)
                        <div>
                            <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-zinc-400">{{ $group }}</p>

                            <div class="space-y-1">
                                @foreach ($items as $item)
                                    @php($active = request()->routeIs($item['active']))

                                    <a
                                        href="{{ route($item['route']) }}"
                                        @if ($active) aria-current="page" @endif
                                        class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-brand-50 text-brand-700' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                                    >
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="shrink-0 border-t border-zinc-100 p-3">
                    <div class="flex items-center gap-3 rounded-lg bg-zinc-50 p-3 ring-1 ring-inset ring-zinc-100">
                        <x-admin.avatar :initials="auth()->user()->initials()" size="sm" tone="brand" />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900">{{ auth()->user()->name }}</p>
                            <p class="truncate text-[11px] text-zinc-500">Library administrator</p>
                        </div>
                    </div>

                    <p class="mt-3 px-1 text-[10px] text-zinc-400">{{ config('app.name', 'myBook') }} · Library admin</p>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white print:hidden">
                    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button type="button" id="sidebar-open" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 lg:hidden" aria-label="Open navigation">
                            Menu
                        </button>

                        <div class="hidden items-center gap-1.5 text-sm sm:flex">
                            <a href="{{ route('admin.dashboard') }}" class="text-zinc-400 transition hover:text-zinc-600">Admin</a>
                            <span class="text-zinc-300">/</span>
                            <span class="font-medium text-zinc-700">{{ $title }}</span>
                        </div>

                        <div class="ml-auto flex items-center gap-1.5">
                            <span class="hidden rounded-lg px-2.5 py-1.5 text-xs font-medium text-zinc-500 md:inline-flex">
                                {{ now('Asia/Jakarta')->format('D, d M Y') }}
                            </span>

                            <div class="mx-1 hidden h-6 w-px bg-zinc-200 sm:block"></div>

                            <details class="relative" data-menu>
                                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-1 pr-1.5 transition hover:bg-zinc-100 [&::-webkit-details-marker]:hidden">
                                    <x-admin.avatar :initials="auth()->user()->initials()" size="sm" tone="brand" />
                                    <span class="hidden max-w-32 truncate text-sm font-medium text-zinc-700 sm:block">{{ auth()->user()->name }}</span>
                                </summary>

                                <div class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg">
                                    <div class="px-4 py-3">
                                        <p class="truncate text-sm font-semibold text-zinc-900">{{ auth()->user()->name }}</p>
                                        <p class="mt-0.5 truncate text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                                        <span class="mt-2 inline-flex items-center rounded-md bg-brand-50 px-2 py-1 text-[11px] font-medium text-brand-700 ring-1 ring-inset ring-brand-100">
                                            Administrator
                                        </span>
                                    </div>

                                    <div class="h-px bg-zinc-100"></div>

                                    <div class="p-1.5">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center rounded-lg px-3 py-2 text-sm text-zinc-700 transition hover:bg-zinc-50">
                                            Account settings
                                        </a>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf

                                            <button type="submit" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50">
                                                Log out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>
                </header>

                <main id="admin-content" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <div class="mx-auto w-full max-w-7xl">
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div class="min-w-0">
                                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ $title }}</h1>

                                @if ($subtitle)
                                    <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500">{{ $subtitle }}</p>
                                @endif
                            </div>

                            @if (isset($actions))
                                <div class="flex flex-wrap items-center gap-2 sm:justify-end print:hidden">
                                    {{ $actions }}
                                </div>
                            @endif
                        </div>

                        <x-admin.flash />

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <script>
            (() => {
                const sidebar = document.getElementById('admin-sidebar');
                const overlay = document.getElementById('admin-overlay');
                const openButton = document.getElementById('sidebar-open');
                const closeButton = document.getElementById('sidebar-close');

                const setSidebar = (open) => {
                    if (!sidebar || !overlay) {
                        return;
                    }

                    sidebar.classList.toggle('-translate-x-full', !open);
                    overlay.classList.toggle('hidden', !open);
                    document.body.classList.toggle('overflow-hidden', open);
                };

                openButton?.addEventListener('click', () => setSidebar(true));
                closeButton?.addEventListener('click', () => setSidebar(false));
                overlay?.addEventListener('click', () => setSidebar(false));

                sidebar?.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) {
                            setSidebar(false);
                        }
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        setSidebar(false);
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        setSidebar(false);
                    }
                });

                document.addEventListener('click', (event) => {
                    document.querySelectorAll('details[data-menu]').forEach((menu) => {
                        if (!menu.contains(event.target)) {
                            menu.removeAttribute('open');
                        }
                    });
                });

                document.querySelectorAll('[data-dismiss]').forEach((button) => {
                    button.addEventListener('click', () => button.closest('[data-flash]')?.remove());
                });

                document.querySelectorAll('form[data-confirm]').forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (!window.confirm(form.dataset.confirm)) {
                            event.preventDefault();
                        }
                    });
                });
            })();
        </script>
    </body>
</html>
