@extends('layouts.admin')

@php $editing = $blog->exists; @endphp
@section('title', $editing ? 'Edit Post' : 'New Post')
@section('breadcrumb')
    <a href="{{ route('admin.blogs.index') }}" class="hover:underline">Blogs</a> / {{ $editing ? 'Edit' : 'New' }}
@endsection

@section('content')
{{--
    IMPORTANT: this page has MANY independent forms (main post, gallery
    upload, SEO panel, GEO panel, FAQs, delete) that live inside one visual
    grid. HTML forms cannot nest, so exactly one <form> tag (#blog-form)
    physically wraps the page; every other form is a real sibling <form>
    placed where it's visually needed, and any input that belongs to
    #blog-form but sits outside it uses the form="blog-form" attribute to
    associate back to it. This is the same pattern used for the Tags page
    in Phase 3 — see PHASE-6-NOTES.md for why the original nested-form
    version was a real bug, not just a lint nitpick.
--}}
<form id="blog-form" method="POST" action="{{ $editing ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($editing) @method('PUT') @endif
</form>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Main column --}}
    <div class="space-y-6 lg:col-span-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium">Title</label>
                <input type="text" name="title" form="blog-form" value="{{ old('title', $blog->title) }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium">Slug <span class="font-normal text-slate-400">(leave blank to auto-generate)</span></label>
                <input type="text" name="slug" form="blog-form" id="slug-input" value="{{ old('slug', $blog->slug) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <p id="slug-preview" class="mt-1 text-xs text-slate-400"></p>
                @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium">Short Description</label>
                <input type="text" name="short_description" form="blog-form" value="{{ old('short_description', $blog->short_description) }}" maxlength="500"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium">Excerpt <span class="font-normal text-slate-400">(auto-filled from content if left blank)</span></label>
                <textarea name="excerpt" form="blog-form" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </div>

            <div>
                <div class="mb-1 flex items-center justify-between">
                    <label class="block text-sm font-medium">Content</label>
                    <span id="editor-stats" class="text-xs text-slate-400"></span>
                </div>
                <!-- <textarea name="content" form="blog-form" id="content-editor" rows="16" required
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('content', $blog->content) }}</textarea> -->
                
                <textarea name="content" form="blog-form" id="content-editor" rows="16"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('content', $blog->content) }}</textarea>

                <p id="content-error" class="mt-1 hidden text-xs text-red-500">
                    Content is required.
                </p>
                @error('content')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                @unless ($editing)
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Save this post once to enable inline image uploads from the toolbar — until then, the image button is disabled.</p>
                @endunless
                <p id="autosave-status" class="mt-1 text-xs text-slate-400"></p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Location context <span class="font-normal text-slate-400">(optional — what the article is about, not the visitor)</span></h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Country</label>
                    <input type="text" name="country" form="blog-form" value="{{ old('country', $blog->country) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Region</label>
                    <input type="text" name="region" form="blog-form" value="{{ old('region', $blog->region) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">City</label>
                    <input type="text" name="city" form="blog-form" value="{{ old('city', $blog->city) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Language</label>
                    <input type="text" name="language" form="blog-form" value="{{ old('language', $blog->language ?? 'en') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
            </div>
        </div>

        @if ($editing)
            @include('admin.blogs.partials.seo-panel', ['blog' => $blog])
            @include('admin.blogs.partials.geo-panel', ['blog' => $blog, 'authors' => $authors])
            @include('admin.blogs.partials.faq-panel', ['blog' => $blog])
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-400">
                SEO, GEO (AI-summary/entities/trust signals), and FAQ panels appear here once this post is saved for the first time.
            </div>
        @endif
    </div>

    {{-- Sidebar column --}}
    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Publish</h3>

            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                <select name="status" form="blog-form" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $blog->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Publish date</label>
                <input type="datetime-local" name="published_at" form="blog-form"
                       value="{{ old('published_at', optional($blog->published_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('published_at')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Schedule date <span class="font-normal">(if Scheduled)</span></label>
                <input type="datetime-local" name="scheduled_at" form="blog-form"
                       value="{{ old('scheduled_at', optional($blog->scheduled_at)->format('Y-m-d\TH:i')) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('scheduled_at')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 border-t border-slate-100 pt-3 text-sm dark:border-slate-800">
                <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" form="blog-form" value="1" @checked(old('is_featured', $blog->is_featured))> Featured</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_trending" form="blog-form" value="1" @checked(old('is_trending', $blog->is_trending))> Trending</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_sticky" form="blog-form" value="1" @checked(old('is_sticky', $blog->is_sticky))> Sticky</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="allow_comments" form="blog-form" value="1" @checked(old('allow_comments', $blog->allow_comments ?? true))> Allow comments</label>
            </div>

            @if ($editing && $blog->seo?->seo_score !== null)
                <div class="mt-4 rounded-lg border border-slate-100 p-3 text-center dark:border-slate-800">
                    <p class="text-2xl font-semibold {{ $blog->seo->seo_score >= 80 ? 'text-emerald-600' : ($blog->seo->seo_score >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $blog->seo->seo_score }}</p>
                    <p class="text-xs text-slate-400">SEO score — see panel below</p>
                </div>
            @endif

            <button type="submit" form="blog-form" class="mt-5 w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                {{ $editing ? 'Save changes' : 'Create post' }}
            </button>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Organize</h3>

            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Category</label>
                <select name="category_id" form="blog-form" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <option value="">— None —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $blog->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @foreach ($cat->children as $child)
                            <option value="{{ $child->id }}" @selected(old('category_id', $blog->category_id) == $child->id)>— {{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Sub-category</label>
                <select name="sub_category_id" form="blog-form" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <option value="">— None —</option>
                    @foreach ($categories as $cat)
                        @foreach ($cat->children as $child)
                            <option value="{{ $child->id }}" @selected(old('sub_category_id', $blog->sub_category_id) == $child->id)>{{ $child->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Author</label>
                <select name="author_id" form="blog-form" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <option value="">— None —</option>
                    @foreach ($authors as $author)
                        <option value="{{ $author->id }}" @selected(old('author_id', $blog->author_id) == $author->id)>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-2">
                <label class="mb-1 block text-xs font-medium text-slate-500">Tags</label>
                <select name="tag_ids[]" form="blog-form" multiple size="5" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" @selected(collect(old('tag_ids', $blog->tags?->pluck('id')->all() ?? []))->contains($tag->id))>{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">New tags <span class="font-normal">(comma-separated)</span></label>
                <input type="text" name="new_tags" form="blog-form" placeholder="e.g. travel, budget-tips"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Featured image</h3>
            @if ($blog->featured_image)
                <img src="{{ media_url($blog->featured_image) }}" alt="" class="mb-3 h-32 w-full rounded-lg object-cover">
            @endif
            <input type="file" name="featured_image" form="blog-form" accept="image/*" class="w-full text-sm">
            @error('featured_image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            <p class="mt-2 text-xs text-slate-400">More sizes, alt/caption/credit fields, and reuse across posts are managed in the <a href="{{ route('admin.media.index') }}" class="text-brand-600 hover:underline">Media Library</a>.</p>
        </div>

        @if ($editing)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="mb-4 text-sm font-semibold">Gallery images</h3>

                <div class="mb-4 grid grid-cols-3 gap-2">
                    @forelse ($blog->images->where('image_type', \App\Enums\ImageType::Gallery) as $img)
                        <div class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                            <img src="{{ $img->url }}" alt="{{ $img->alt_text }}" class="h-full w-full object-cover">
                            <form method="POST" action="{{ route('admin.blogs.images.destroy', [$blog, $img]) }}" onsubmit="return confirm('Remove this image?')"
                                  class="absolute inset-x-0 bottom-0 hidden bg-black/60 p-1 text-center group-hover:block">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[10px] font-medium text-red-300 hover:underline">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="col-span-3 text-xs text-slate-400">No gallery images yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.blogs.images.store', $blog) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <input type="hidden" name="image_type" value="gallery">
                    <input type="file" name="image" accept="image/*" required class="w-full text-xs">
                    <input type="text" name="alt_text" placeholder="Alt text" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800">
                    <input type="text" name="credit" placeholder="Credit (optional)" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800">
                    <button type="submit" class="w-full rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white dark:bg-slate-700">Add to gallery</button>
                </form>
                <p class="mt-2 text-xs text-slate-400">Insert an image directly into the content above via the editor's toolbar — it uploads through this same pipeline as an inline image.</p>
            </div>
        @endif

        @if ($editing)
            <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this post? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950">
                    Delete post
                </button>
            </form>
        @endif
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (function () {
            const blogId = {{ $editing ? $blog->id : 'null' }};
            const uploadUrl = blogId ? '{{ $editing ? route('admin.blogs.images.store', $blog) : '' }}' : null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const galleryImages = @json($editing ? $blog->images->where('image_type', 'gallery')->values()->map(fn($i) => ['url' => $i->url, 'alt' => $i->alt_text]) : []);

            // ---- Very small markdown → HTML pass for pasted content -------
            // Covers the common cases (headers, bold/italic, links, unordered
            // lists, inline code) — not a full CommonMark implementation, but
            // enough that pasting from a markdown note doesn't dump raw
            // asterisks/hashes into the post.
            function markdownToHtml(text) {
                let html = text
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                    .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                    .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/`([^`]+)`/g, '<code>$1</code>')
                    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
                    .replace(/^\s*[-*]\s+(.*$)/gim, '<li>$1</li>');

                html = html.replace(/(<li>.*<\/li>\n?)+/g, (m) => '<ul>' + m + '</ul>');

                return html.split(/\n{2,}/).map(p =>
                    /^<(h[1-3]|ul|li)/.test(p.trim()) ? p : `<p>${p.trim()}</p>`
                ).join('\n');
            }

            function looksLikeMarkdown(text) {
                return /^#{1,3}\s|\*\*[^*]+\*\*|^\s*[-*]\s|\[[^\]]+\]\([^)]+\)/m.test(text);
            }

            tinymce.init({
                selector: '#content-editor',
                license_key: 'gpl',
                height: 550,
                menubar: 'edit view insert format tools table help',
                plugins: 'lists link image table code codesample quickbars wordcount autolink media fullscreen preview help searchreplace',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
                    'bullist numlist outdent indent | link image mediaEmbed tablebtn | ' +
                    'blockquote calloutBtn codesample code | fullscreen preview',
                toolbar_mode: 'sliding',
                content_style: 'body { font-family: system-ui, sans-serif; font-size: 15px; } ' +
                    '.callout { border-left: 4px solid #4f46e5; background: #eef2ff; padding: 12px 16px; border-radius: 6px; margin: 12px 0; } ' +
                    'blockquote { border-left: 3px solid #cbd5e1; margin-left: 0; padding-left: 16px; color: #475569; }',

                // ---- Image upload: every inserted/dragged/pasted image goes
                // through the same Phase 4 pipeline as the gallery panel ----
                images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                    if (! uploadUrl) {
                        reject('Save the post first to enable image uploads.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    formData.append('image_type', 'inline');

                    fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    })
                        .then(res => res.ok ? res.json() : Promise.reject('Upload failed'))
                        .then(data => resolve(data.image.url))
                        .catch(reject);
                }),
                automatic_uploads: true,
                images_reuse_filename: true,

                // ---- Markdown paste detection ----
                paste_preprocess: (plugin, args) => {
                    if (looksLikeMarkdown(args.content)) {
                        args.content = markdownToHtml(args.content);
                    }
                },

                // ---- Custom "Table" / "Callout" toolbar buttons ----
                setup: (editor) => {
                    editor.ui.registry.addButton('tablebtn', {
                        icon: 'table',
                        tooltip: 'Insert table',
                        onAction: () => editor.execCommand('mceInsertTable'),
                    });

                    editor.ui.registry.addButton('calloutBtn', {
                        text: 'Callout',
                        tooltip: 'Insert a highlighted callout box',
                        onAction: () => {
                            editor.insertContent('<div class="callout">' + (editor.selection.getContent() || 'Callout text…') + '</div>');
                        },
                    });

                    editor.ui.registry.addButton('mediaEmbed', {
                        icon: 'embed',
                        tooltip: 'Embed YouTube / Vimeo',
                        onAction: () => {
                            editor.windowManager.open({
                                title: 'Embed video',
                                body: {
                                    type: 'panel',
                                    items: [{ type: 'input', name: 'url', label: 'YouTube or Vimeo URL' }],
                                },
                                buttons: [
                                    { type: 'cancel', text: 'Cancel' },
                                    { type: 'submit', text: 'Embed', primary: true },
                                ],
                                onSubmit: (api) => {
                                    const url = api.getData().url;
                                    const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
                                    const vimeo = url.match(/vimeo\.com\/(\d+)/);
                                    let embed = '';
                                    if (yt) {
                                        embed = `<iframe width="100%" height="360" src="https://www.youtube.com/embed/${yt[1]}" frameborder="0" allowfullscreen></iframe>`;
                                    } else if (vimeo) {
                                        embed = `<iframe width="100%" height="360" src="https://player.vimeo.com/video/${vimeo[1]}" frameborder="0" allowfullscreen></iframe>`;
                                    }
                                    if (embed) editor.insertContent(embed);
                                    api.close();
                                },
                            });
                        },
                    });

                    if (galleryImages.length) {
                        editor.ui.registry.addButton('galleryPicker', {
                            icon: 'gallery',
                            tooltip: 'Insert from this post\'s gallery',
                            onAction: () => {
                                const items = galleryImages.map((img, i) =>
                                    `<img data-idx="${i}" src="${img.url}" style="width:80px;height:80px;object-fit:cover;margin:4px;cursor:pointer;border-radius:6px;">`
                                ).join('');
                                editor.windowManager.open({
                                    title: 'Insert from gallery',
                                    body: { type: 'panel', items: [{ type: 'htmlpanel', html: `<div id="gallery-picker">${items}</div>` }] },
                                    buttons: [{ type: 'cancel', text: 'Close' }],
                                    onAction: () => {},
                                });
                                setTimeout(() => {
                                    document.querySelectorAll('#gallery-picker img').forEach(el => {
                                        el.addEventListener('click', () => {
                                            const img = galleryImages[el.dataset.idx];
                                            editor.insertContent(`<img src="${img.url}" alt="${img.alt || ''}">`);
                                            editor.windowManager.getWindows()[0]?.close();
                                        });
                                    });
                                }, 50);
                            },
                        });
                    }

                    // ---- Word count / reading time in the header stats line ----
                    const updateStats = () => {
                        const count = editor.plugins.wordcount.body.getWordCount();
                        const minutes = Math.max(1, Math.round(count / 200));
                        document.getElementById('editor-stats').textContent = `${count} words · ~${minutes} min read`;
                    };
                    editor.on('keyup change SetContent', updateStats);
                    editor.on('init', updateStats);

                    // ---- Client-side autosave (recovery only — not a
                    // published draft; see PHASE-5-NOTES.md) ----
                    const autosaveKey = 'blog-autosave-' + (blogId ?? 'new');
                    let dirty = false;
                    editor.on('input', () => { dirty = true; });

                    setInterval(() => {
                        if (! dirty) return;
                        localStorage.setItem(autosaveKey, JSON.stringify({
                            content: editor.getContent(),
                            title: document.querySelector('[name="title"]').value,
                            savedAt: new Date().toISOString(),
                        }));
                        document.getElementById('autosave-status').textContent =
                            'Draft auto-saved locally at ' + new Date().toLocaleTimeString();
                        dirty = false;
                    }, 15000);

                    editor.on('init', () => {
                        const saved = localStorage.getItem(autosaveKey);
                        if (! saved) return;
                        const parsed = JSON.parse(saved);
                        const currentContent = editor.getContent();
                        if (parsed.content && parsed.content !== currentContent &&
                            confirm('A locally auto-saved draft from ' + new Date(parsed.savedAt).toLocaleString() + ' was found. Restore it?')) {
                            editor.setContent(parsed.content);
                            if (parsed.title) document.querySelector('[name="title"]').value = parsed.title;
                        }
                    });
                },
            });

            // ---- Live slug preview (actual slug is still generated
            // server-side by HasSlug if left blank — this is cosmetic) ----
            const titleInput = document.querySelector('[name="title"]');
            const slugInput = document.getElementById('slug-input');
            const slugPreview = document.getElementById('slug-preview');
            const slugify = (str) => str.toLowerCase().trim()
                .replace(/[^\w\s-]/g, '').replace(/[\s_]+/g, '-').replace(/^-+|-+$/g, '');

            titleInput?.addEventListener('input', () => {
                if (! slugInput.value) {
                    slugPreview.textContent = titleInput.value ? 'Will be saved as: ' + slugify(titleInput.value) : '';
                }
            });
        })();
    </script>
@endpush
@endsection
