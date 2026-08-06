@extends('layouts.guest', ['title' => 'Sign in'])

@section('content')
    <h1 class="mb-6 text-lg font-medium text-white">Sign in to your account</h1>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-300">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-600 bg-slate-800 text-brand-600 focus:ring-brand-600">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-brand-600 hover:text-brand-700">Forgot password?</a>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
            Sign in
        </button>
    </form>
@endsection
