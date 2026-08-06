<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sign in' }} — {{ config('app.name', 'Blog CMS') }}</title>

    {{-- Tailwind loaded from CDN by design: the spec requires this app to run
         on shared hosting with no Node runtime in production. The Play CDN
         build needs zero compile step. Swap for a compiled stylesheet later
         if you add a local Node toolchain for development. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="flex min-h-full flex-col items-center justify-center px-4 py-12">
        <div class="mb-8 flex items-center gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold">B</div>
            <span class="text-lg font-semibold">{{ config('app.name', 'Blog CMS') }}</span>
        </div>

        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-sm">
            {{ $slot }}
        </div>

        <p class="mt-8 text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('app.name', 'Blog CMS') }}. All rights reserved.</p>
    </div>
</body>
</html>
