<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ sidebarOpen: window.innerWidth >= 1024, dark: true }"
      x-init="window.addEventListener('resize', () => { if (window.innerWidth < 1024) sidebarOpen = false })"
      :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Blog CMS') }}</title>

    {{-- Real compiled Tailwind build (run `npm install && npm run build:css`
         once locally — see PHASE-9-NOTES.md) takes over the moment it
         exists; falls back to the Play CDN so the panel never looks broken
         on a fresh clone before that first build. Nothing on the shared
         hosting server needs Node either way — only the static output file
         (public/build/app.css) gets uploaded. --}}
    @if (file_exists(public_path('build/app.css')))
        <link rel="stylesheet" href="{{ asset('build/app.css') }}">
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: { extend: { colors: { brand: { 50:'#eef2ff',600:'#4f46e5',700:'#4338ca' } } } }
            }
        </script>
    @endif
    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
    {{-- Mirrors the [x-cloak] rule in resources/css/app.css — needed here
         too since the CDN fallback path above never loads that file. --}}
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen">
        {{-- Sidebar — sticky so it stays in view while the main content scrolls --}}
        <aside x-show="sidebarOpen" x-transition
               class="sticky top-0 h-screen w-64 shrink-0 overflow-y-auto border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-5 dark:border-slate-800">
                <span class="text-lg font-semibold">{{ config('app.name', 'Blog CMS') }}</span>
            </div>
            <nav class="space-y-1 px-3 py-4 text-sm">
                @php
                    $navLink = function (string $route, string $label) {
                        $active = request()->routeIs($route.'*');
                        $classes = $active
                            ? 'bg-brand-50 text-brand-700 dark:bg-slate-800 dark:text-white'
                            : 'text-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:text-slate-300 dark:hover:bg-slate-800';

                        return '<a href="'.route($route).'" class="flex items-center gap-2 rounded-lg px-3 py-2 font-medium '.$classes.'">'.$label.'</a>';
                    };
                @endphp

                {!! $navLink('admin.dashboard', 'Dashboard') !!}
                {!! $navLink('admin.blogs.index', 'Blogs') !!}
                {!! $navLink('admin.categories.index', 'Categories') !!}
                {!! $navLink('admin.tags.index', 'Tags') !!}
                {!! $navLink('admin.authors.index', 'Authors') !!}
                {!! $navLink('admin.media.index', 'Media Library') !!}
                {!! $navLink('admin.comments.index', 'Comments') !!}
                {!! $navLink('admin.pages.index', 'Pages') !!}
                {!! $navLink('admin.menus.index', 'Menus') !!}
                {!! $navLink('admin.redirects.index', 'Redirects') !!}
                @if (auth()->user()->hasPermissionTo('users.view'))
                    {!! $navLink('admin.users.index', 'Users') !!}
                @endif
                @if (auth()->user()->hasPermissionTo('settings.view'))
                    {!! $navLink('admin.settings.edit', 'Settings') !!}
                @endif
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-md p-2 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Toggle sidebar">
                        ☰
                    </button>
                    @hasSection('breadcrumb')
                        <nav class="hidden text-sm text-slate-500 dark:text-slate-400 lg:block">@yield('breadcrumb')</nav>
                    @endif
                </div>

                {{-- Search Everywhere --}}
                <div class="mx-4 hidden max-w-md flex-1 lg:block" x-data="globalSearch()">
                    <div class="relative">
                        <input type="text" x-model="query" @input.debounce.300ms="search()" @focus="open = true" @keydown.escape="open = false"
                               placeholder="Search everywhere… (blogs, pages, categories…)"
                               class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-1.5 text-sm focus:border-brand-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-600 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-800">

                        <div x-show="open && query.length >= 2" @click.outside="open = false" x-transition x-cloak
                             class="absolute left-0 right-0 top-full z-40 mt-2 max-h-96 overflow-y-auto rounded-lg border border-slate-200 bg-white py-2 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                            <template x-if="loading">
                                <p class="px-4 py-3 text-xs text-slate-400">Searching…</p>
                            </template>
                            <template x-if="! loading && Object.keys(results).length === 0">
                                <p class="px-4 py-3 text-xs text-slate-400">No matches.</p>
                            </template>
                            <template x-for="group in Object.keys(results)" :key="group">
                                <div class="mb-1">
                                    <p class="px-4 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400" x-text="group"></p>
                                    <template x-for="item in results[group]" :key="item.url">
                                        <a :href="item.url" class="block px-4 py-1.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-800" x-text="item.label"></a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Quick Actions --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1 rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">
                            + New
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition x-cloak
                             class="absolute right-0 mt-2 w-44 rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg dark:border-slate-800 dark:bg-slate-900">
                            @can('create', \App\Models\Blog::class)
                                <a href="{{ route('admin.blogs.create') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">New Post</a>
                            @endcan
                            @can('create', \App\Models\Page::class)
                                <a href="{{ route('admin.pages.create') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">New Page</a>
                            @endcan
                            @can('create', \App\Models\Category::class)
                                <a href="{{ route('admin.categories.create') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">New Category</a>
                            @endcan
                            @can('create', \App\Models\Author::class)
                                <a href="{{ route('admin.authors.create') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">New Author</a>
                            @endcan
                        </div>
                    </div>

                    <button @click="dark = !dark" class="rounded-md p-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                        <span x-show="!dark">🌙</span><span x-show="dark">☀️</span>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg dark:border-slate-800 dark:bg-slate-900">
                            <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left hover:bg-slate-100 dark:hover:bg-slate-800">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Toast notifications — replaces the old static flash-message banner.
         Reads the same session('status') flash key every controller already
         sets; only the presentation changed. --}}
    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-lg bg-slate-900 px-4 py-3 text-sm text-white shadow-xl dark:bg-white dark:text-slate-900">
            @php
                $statusMessages = [
                    'profile-updated' => 'Your profile has been updated.',
                    'password-updated' => 'Your password has been updated.',
                ];
            @endphp
            <span>{{ $statusMessages[session('status')] ?? \Illuminate\Support\Str::headline(session('status')) }}</span>
            <button @click="show = false" class="text-white/60 hover:text-white dark:text-slate-500 dark:hover:text-slate-900">✕</button>
        </div>
    @endif

    @stack('scripts')

    <script>
        function globalSearch() {
            return {
                query: '',
                results: {},
                loading: false,
                open: false,
                search() {
                    if (this.query.length < 2) { this.results = {}; return; }
                    this.loading = true;
                    fetch('{{ route('admin.search') }}?q=' + encodeURIComponent(this.query))
                        .then(res => res.json())
                        .then(data => { this.results = data.results; this.loading = false; })
                        .catch(() => { this.loading = false; });
                },
            };
        }
    </script>
</body>
</html>
