@php $seo = $blog->seo; @endphp
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="seoPanel()">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold">SEO</h3>
        @if ($seo?->seo_score !== null)
            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $seo->seo_score >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($seo->seo_score >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400') }}">
                Score: {{ $seo->seo_score }}/100
            </span>
        @endif
    </div>

    @if ($errors->seo->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->seo->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if ($seo?->seo_suggestions)
        <div class="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
            <p class="mb-1 font-medium">Suggestions:</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($seo->seo_suggestions as $suggestion)<li>{{ $suggestion }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Live Google SERP preview --}}
    <div class="mb-5 rounded-lg border border-slate-200 p-3 dark:border-slate-700">
        <p class="mb-2 text-xs font-medium text-slate-400">Google preview</p>
        <p class="truncate text-sm text-blue-700 dark:text-blue-400" x-text="metaTitle || '{{ addslashes($blog->title) }}'"></p>
        <p class="text-xs text-emerald-700 dark:text-emerald-500">{{ url('/blog/'.$blog->slug) }}</p>
        <p class="line-clamp-2 text-xs text-slate-500" x-text="metaDescription || '{{ addslashes(\Illuminate\Support\Str::limit(strip_tags($blog->excerpt ?? ''), 160)) }}'"></p>
    </div>

    <form method="POST" action="{{ route('admin.blogs.seo.update', $blog) }}" enctype="multipart/form-data" class="space-y-4" @submit="">
        @csrf @method('PUT')

        <div>
            <div class="mb-1 flex justify-between text-xs">
                <label class="font-medium">Meta title</label>
                <span x-text="(metaTitle || '').length + ' / 60'"></span>
            </div>
            <input type="text" name="meta_title" x-model="metaTitle" value="{{ old('meta_title', $seo?->meta_title) }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>

        <div>
            <div class="mb-1 flex justify-between text-xs">
                <label class="font-medium">Meta description</label>
                <span x-text="(metaDescription || '').length + ' / 160'"></span>
            </div>
            <textarea name="meta_description" x-model="metaDescription" rows="2" maxlength="320"
                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('meta_description', $seo?->meta_description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Focus keyword</label>
                <input type="text" name="focus_keyword" value="{{ old('focus_keyword', $seo?->focus_keyword) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Secondary keywords <span class="font-normal">(comma-separated)</span></label>
                <input type="text" name="secondary_keywords" value="{{ old('secondary_keywords', is_array($seo?->secondary_keywords) ? implode(', ', $seo->secondary_keywords) : '') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Canonical URL</label>
                <input type="url" name="canonical_url" value="{{ old('canonical_url', $seo?->canonical_url) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Robots</label>
                <select name="robots" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    @foreach (['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'] as $option)
                        <option value="{{ $option }}" @selected(old('robots', $seo?->robots ?? 'index,follow') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <p class="text-xs text-slate-400">Meta keywords field also exists (<code>meta_keywords</code>) but modern search engines ignore it — omitted from this form to avoid encouraging keyword stuffing; it's still in the schema if you need it via the API.</p>

        <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
            <h4 class="mb-3 text-xs font-semibold uppercase text-slate-400">Social sharing</h4>
            <div class="mb-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Open Graph title</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $seo?->og_title) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Twitter title</label>
                    <input type="text" name="twitter_title" value="{{ old('twitter_title', $seo?->twitter_title) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
            </div>
            <div class="mb-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Open Graph description</label>
                    <textarea name="og_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('og_description', $seo?->og_description) }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Twitter description</label>
                    <textarea name="twitter_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('twitter_description', $seo?->twitter_description) }}</textarea>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Open Graph image</label>
                    @if ($seo?->og_image)<img src="{{ media_url($seo->og_image) }}" alt="" class="mb-2 h-16 rounded">@endif
                    <input type="file" name="og_image" accept="image/*" class="w-full text-xs">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Twitter image</label>
                    @if ($seo?->twitter_image)<img src="{{ media_url($seo->twitter_image) }}" alt="" class="mb-2 h-16 rounded">@endif
                    <input type="file" name="twitter_image" accept="image/*" class="w-full text-xs">
                </div>
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save SEO</button>
    </form>
</div>

<script>
    function seoPanel() {
        return {
            metaTitle: {{ Js::from(old('meta_title', $seo?->meta_title ?? '')) }},
            metaDescription: {{ Js::from(old('meta_description', $seo?->meta_description ?? '')) }},
        };
    }
</script>
