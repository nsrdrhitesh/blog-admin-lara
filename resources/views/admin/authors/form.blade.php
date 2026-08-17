@extends('layouts.admin')

@php $editing = $author->exists; @endphp
@section('title', $editing ? 'Edit Author' : 'New Author')
@section('breadcrumb')
    <a href="{{ route('admin.authors.index') }}" class="hover:underline">Authors</a> / {{ $editing ? 'Edit' : 'New' }}
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.authors.update', $author) : route('admin.authors.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4 flex items-center gap-4">
            @if ($author->photo)
                <img src="{{ media_url($author->photo) }}" alt="" class="h-16 w-16 rounded-full object-cover">
            @endif
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium">Photo</label>
                <input type="file" name="photo" accept="image/*" class="w-full text-sm">
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name', $author->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Slug <span class="font-normal text-slate-400">(leave blank to auto-generate)</span></label>
            <input type="text" name="slug" value="{{ old('slug', $author->slug) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium">Designation</label>
                <input type="text" name="designation" value="{{ old('designation', $author->designation) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Years of experience</label>
                <input type="number" name="experience_years" value="{{ old('experience_years', $author->experience_years) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Bio</label>
            <textarea name="bio" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('bio', $author->bio) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Website</label>
            <input type="url" name="website" value="{{ old('website', $author->website) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            @foreach (['twitter', 'linkedin', 'facebook', 'instagram'] as $network)
                <div>
                    <label class="mb-1 block text-sm font-medium capitalize">{{ $network }}</label>
                    <input type="url" name="social_links[{{ $network }}]" value="{{ old('social_links.'.$network, $author->social_links[$network] ?? '') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
            @endforeach
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Skills <span class="font-normal text-slate-400">(comma-separated)</span></label>
            <input type="text" name="skills" value="{{ old('skills', is_array($author->skills) ? implode(', ', $author->skills) : '') }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $author->is_active ?? true))> Active
        </label>
    </div>

    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
        {{ $editing ? 'Save changes' : 'Create author' }}
    </button>
</form>
@endsection
