@extends('layouts.guest', ['title' => 'Forgot password'])

@section('content')
    <h1 class="mb-2 text-lg font-medium text-white">Forgot your password?</h1>
    <p class="mb-6 text-sm text-slate-400">Enter your email and we'll send you a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
            Email reset link
        </button>

        <p class="text-center text-sm text-slate-400">
            <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700">Back to sign in</a>
        </p>
    </form>
@endsection
