@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Posts', 'value' => '—'],
            ['label' => 'Published', 'value' => '—'],
            ['label' => 'Drafts', 'value' => '—'],
            ['label' => 'Comments Pending', 'value' => '—'],
        ] as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-semibold">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
        Welcome, {{ auth()->user()->name }}. Dashboard widgets (recent posts, top content,
        traffic charts) are wired up once the Blog module ships in Phase 3.
    </div>
@endsection
