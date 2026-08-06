<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: true, dark: true }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Blog CMS') }}</title>

    {{-- Play CDN for now — swap for a compiled build/app.css before production
         (see PHASE-2-NOTES.md). Alpine is loaded for the sidebar/dropdown toggles only. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: { 50:'#eef2ff',600:'#4f46e5',700:'#4338ca' } } } }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside x-show="sidebarOpen" x-transition
               class="w-64 shrink-0 border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-5 dark:border-slate-800">
                <span class="text-lg font-semibold">{{ config('app.name', 'Blog CMS') }}</span>
            </div>
            <nav class="space-y-1 px-3 py-4 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    Dashboard
                </a>
                {{-- Blogs / Categories / Tags / Media / Comments / Users / Settings links
                     are added here as each module's routes land in later phases. --}}
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            {{-- Topbar --}}
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="rounded-md p-2 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Toggle sidebar">
                        ☰
                    </button>
                    @hasSection('breadcrumb')
                        <nav class="text-sm text-slate-500 dark:text-slate-400">@yield('breadcrumb')</nav>
                    @endif
                </div>

                <div class="flex items-center gap-4">
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
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30">
                        @php
                            $statusMessages = [
                                'profile-updated' => 'Your profile has been updated.',
                                'password-updated' => 'Your password has been updated.',
                            ];
                        @endphp
                        {{ $statusMessages[session('status')] ?? session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
