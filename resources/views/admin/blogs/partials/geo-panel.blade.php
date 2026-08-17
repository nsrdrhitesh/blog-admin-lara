@php $geo = $blog->geoMeta; @endphp
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="mb-1 text-sm font-semibold">GEO — Generative Engine Optimization</h3>
    <p class="mb-4 text-xs text-slate-400">Helps AI search engines (ChatGPT, Perplexity, AI Overviews) summarize and cite this post accurately.</p>

    @if ($errors->geo->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->geo->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.blogs.geo.update', $blog) }}" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">AI summary <span class="font-normal">(a few sentences an AI engine can quote directly)</span></label>
            <textarea name="ai_summary" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('ai_summary', $geo?->ai_summary) }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Short AI summary <span class="font-normal">(one sentence)</span></label>
            <input type="text" name="short_ai_summary" maxlength="500" value="{{ old('short_ai_summary', $geo?->short_ai_summary) }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Key takeaways <span class="font-normal">(one per line)</span></label>
                <textarea name="key_takeaways" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('key_takeaways', is_array($geo?->key_takeaways) ? implode("\n", $geo->key_takeaways) : '') }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Highlights <span class="font-normal">(one per line)</span></label>
                <textarea name="highlights" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('highlights', is_array($geo?->highlights) ? implode("\n", $geo->highlights) : '') }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Pros <span class="font-normal">(one per line)</span></label>
                <textarea name="pros" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('pros', is_array($geo?->pros) ? implode("\n", $geo->pros) : '') }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Cons <span class="font-normal">(one per line)</span></label>
                <textarea name="cons" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('cons', is_array($geo?->cons) ? implode("\n", $geo->cons) : '') }}</textarea>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Entities <span class="font-normal">(one per line, in each box)</span></label>
            <div class="grid grid-cols-3 gap-3">
                @foreach (['people' => 'People', 'companies' => 'Companies', 'products' => 'Products', 'locations' => 'Locations', 'events' => 'Events', 'topics' => 'Topics'] as $key => $label)
                    <div>
                        <label class="mb-1 block text-[11px] text-slate-400">{{ $label }}</label>
                        <textarea name="entities[{{ $key }}]" rows="2" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800">{{ old("entities.$key", is_array($geo?->entities[$key] ?? null) ? implode("\n", $geo->entities[$key]) : '') }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">References / citation URLs <span class="font-normal">(one per line)</span></label>
            <textarea name="citation_urls" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('citation_urls', is_array($geo?->citation_urls) ? implode("\n", $geo->citation_urls) : '') }}</textarea>
        </div>

        <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
            <h4 class="mb-3 text-xs font-semibold uppercase text-slate-400">E-E-A-T trust signals</h4>
            <div class="mb-3 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Reviewer</label>
                    <select name="reviewer_author_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">— None —</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}" @selected(old('reviewer_author_id', $geo?->reviewer_author_id) == $author->id)>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Reviewed date</label>
                    <input type="date" name="reviewed_date" value="{{ old('reviewed_date', optional($geo?->reviewed_date)->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach (['experience_signal' => 'Experience', 'expertise_signal' => 'Expertise', 'authority_signal' => 'Authority', 'trust_signal' => 'Trustworthiness'] as $field => $label)
                    <div>
                        <label class="mb-1 block text-[11px] text-slate-400">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800">{{ old($field, $geo?->{$field}) }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
            <label class="mb-2 flex items-center gap-2 text-sm">
                <input type="checkbox" name="speakable_enabled" value="1" @checked(old('speakable_enabled', $geo?->speakable_enabled))> Enable Speakable schema (voice assistants)
            </label>
            <label class="mb-1 block text-xs font-medium text-slate-500">Speakable CSS selectors <span class="font-normal">(one per line, e.g. .post-title)</span></label>
            <textarea name="speakable_selectors" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('speakable_selectors', is_array($geo?->speakable_selectors) ? implode("\n", $geo->speakable_selectors) : '') }}</textarea>
        </div>

        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save GEO settings</button>
    </form>
</div>
