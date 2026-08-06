@extends('layouts.guest', ['title' => 'Reset password'])

@section('content')
    <h1 class="mb-6 text-lg font-medium text-white">Choose a new password</h1>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-300">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-300">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
            Reset password
        </button>
    </form>
@endsection
