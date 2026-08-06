<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Blog CMS' }}</title>
    {{-- Play CDN for now — swap for a compiled build/app.css once Tailwind CLI
         is run locally (see PHASE-2-NOTES.md). No Node required on the server. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 600: '#4f46e5', 700: '#4338ca' } } } } }
    </script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <span class="text-2xl font-semibold tracking-tight text-white">{{ config('app.name', 'Blog CMS') }}</span>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-8 shadow-2xl shadow-black/40 backdrop-blur">
                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 ring-1 ring-emerald-500/30">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-500/10 px-4 py-3 text-sm text-red-400 ring-1 ring-red-500/30">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
