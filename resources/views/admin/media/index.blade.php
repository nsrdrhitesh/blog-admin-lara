@extends('layouts.admin')

@section('title', 'Media Library')
@section('breadcrumb', 'Media Library')

@section('content')
<div x-data="mediaLibrary()" class="grid grid-cols-1 gap-6 lg:grid-cols-4">

    {{-- Folder sidebar --}}
    <div class="space-y-4 lg:col-span-1">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-3 text-sm font-semibold">Folders</h3>
            <nav class="space-y-1 text-sm">
                <a href="{{ route('admin.media.index') }}"
                   class="block rounded-lg px-2 py-1.5 {{ ! request('folder_id') ? 'bg-brand-50 text-brand-700 dark:bg-slate-800 dark:text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    All files
                </a>
                @foreach ($folders as $folder)
                    <a href="{{ route('admin.media.index', ['folder_id' => $folder->id]) }}"
                       class="block rounded-lg px-2 py-1.5 {{ request('folder_id') == $folder->id ? 'bg-brand-50 text-brand-700 dark:bg-slate-800 dark:text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        📁 {{ $folder->name }}
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('admin.media-folders.store') }}" class="mt-4 flex gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                @csrf
                <input type="text" name="name" placeholder="New folder…" required
                       class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800">
                <button type="submit" class="rounded-lg bg-slate-800 px-2.5 text-xs font-medium text-white dark:bg-slate-700">+</button>
            </form>
        </div>

        {{-- Drag & drop upload --}}
        <div
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="handleDrop($event)"
            :class="dragging ? 'border-brand-600 bg-brand-50 dark:bg-slate-800' : 'border-slate-300 dark:border-slate-700'"
            class="rounded-xl border-2 border-dashed p-6 text-center transition">
            <p class="text-sm text-slate-500">Drag & drop images here</p>
            <p class="my-2 text-xs text-slate-400">or</p>
            <label class="cursor-pointer rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                Browse files
                <input type="file" multiple accept="image/*" class="hidden" @change="handleSelect($event)">
            </label>
            <p x-show="uploading" x-cloak class="mt-3 text-xs text-brand-600">Uploading…</p>
        </div>
    </div>

    {{-- Grid --}}
    <div class="lg:col-span-3">
        <form method="GET" class="mb-4 flex gap-3">
            <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search media…"
                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-1.5 text-sm font-medium text-white dark:bg-slate-700">Search</button>
        </form>

        <div class="mb-3 flex items-center justify-between">
            <span class="text-sm text-slate-500" x-show="selected.length > 0" x-cloak x-text="selected.length + ' selected'"></span>
            <form method="POST" action="{{ route('admin.media.bulk-delete') }}" @submit="return confirm('Delete selected files?')" x-show="selected.length > 0" x-cloak>
                @csrf @method('DELETE')
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="rounded-lg border border-red-300 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950">
                    Delete selected
                </button>
            </form>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
            @forelse ($media as $item)
                <div class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <label class="absolute left-2 top-2 z-10">
                        <input type="checkbox" value="{{ $item->id }}" @change="toggleSelect({{ $item->id }}, $event)"
                               class="rounded border-slate-300 bg-white/90">
                    </label>

                    <button type="button" @click="openPreview({{ $item->id }})" x-data="{ loaded: false }"
                            :class="{ shimmer: ! loaded }" class="block aspect-square w-full">
                        <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" loading="lazy"
                             @load="loaded = true" x-show="loaded" x-cloak
                             class="h-full w-full object-cover">
                    </button>

                    <div class="p-2">
                        <p class="truncate text-xs font-medium">{{ $item->original_name }}</p>
                        <p class="text-[10px] text-slate-400">{{ format_bytes($item->file_size ?? 0) }} @if($item->reuse_count) · reused {{ $item->reuse_count }}x @endif</p>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 flex justify-center gap-2 bg-black/50 p-2 opacity-0 transition group-hover:opacity-100">
                        <button type="button" @click="openPreview({{ $item->id }})" class="text-xs font-medium text-white hover:underline">Edit</button>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete this file?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-300 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="col-span-full py-16 text-center text-slate-400">No media yet — drag some images in to get started.</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $media->links() }}</div>
    </div>

    {{-- Preview / edit-metadata modal --}}
    <div x-show="previewId" x-cloak @keydown.escape.window="previewId = null"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div @click.outside="previewId = null" class="w-full max-w-lg rounded-xl bg-white p-6 dark:bg-slate-900">
            <template x-for="item in [items[previewId]]" :key="previewId">
                <div x-show="item">
                    <img :src="item?.url" alt="" class="mb-4 h-48 w-full rounded-lg object-cover">
                    <form :action="'/admin/media/' + previewId" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Alt text</label>
                            <input type="text" name="alt_text" :value="item?.alt_text" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Caption</label>
                            <input type="text" name="caption" :value="item?.caption" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Credit</label>
                            <input type="text" name="credit" :value="item?.credit" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div class="mb-4">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Source URL</label>
                            <input type="url" name="source_url" :value="item?.source_url" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="previewId = null" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700">Cancel</button>
                            <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function mediaLibrary() {
        return {
            dragging: false,
            uploading: false,
            selected: [],
            previewId: null,
            items: @json($media->getCollection()->keyBy('id')),

            toggleSelect(id, event) {
                if (event.target.checked) {
                    this.selected.push(id);
                } else {
                    this.selected = this.selected.filter(i => i !== id);
                }
            },

            openPreview(id) {
                this.previewId = id;
            },

            handleDrop(event) {
                this.dragging = false;
                this.upload(event.dataTransfer.files);
            },

            handleSelect(event) {
                this.upload(event.target.files);
            },

            upload(fileList) {
                if (! fileList.length) return;

                const formData = new FormData();
                for (const file of fileList) formData.append('files[]', file);
                const folderId = new URLSearchParams(window.location.search).get('folder_id');
                if (folderId) formData.append('folder_id', folderId);

                this.uploading = true;

                fetch('{{ route('admin.media.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                })
                    .then(res => res.json())
                    .then(() => window.location.reload())
                    .catch(() => { this.uploading = false; alert('Upload failed — check file type/size.'); });
            },
        };
    }
</script>
@endsection
