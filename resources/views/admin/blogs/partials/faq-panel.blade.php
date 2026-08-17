<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="mb-1 text-sm font-semibold">FAQs</h3>
    <p class="mb-4 text-xs text-slate-400">Powers the FAQ JSON-LD schema and gives AI/search engines direct question-answer pairs to surface.</p>

    @if ($errors->faq->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->faq->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 space-y-3">
        @forelse ($blog->faqs as $faq)
            @php $formId = 'faq-form-'.$faq->id; @endphp
            <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                <input type="text" name="question" form="{{ $formId }}" value="{{ $faq->question }}" placeholder="Question"
                       class="mb-2 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm font-medium dark:border-slate-700 dark:bg-slate-800">
                <textarea name="answer" form="{{ $formId }}" rows="2" placeholder="Answer"
                          class="mb-2 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">{{ $faq->answer }}</textarea>
                <div class="flex justify-end gap-3 text-xs">
                    <button type="submit" form="{{ $formId }}" class="text-brand-600 hover:underline">Save</button>
                    <button type="submit" form="delete-{{ $formId }}" class="text-red-600 hover:underline">Delete</button>
                </div>
            </div>
            <form id="{{ $formId }}" method="POST" action="{{ route('admin.blogs.faqs.update', [$blog, $faq]) }}" class="hidden">
                @csrf @method('PUT')
            </form>
            <form id="delete-{{ $formId }}" method="POST" action="{{ route('admin.blogs.faqs.destroy', [$blog, $faq]) }}" class="hidden" onsubmit="return confirm('Delete this FAQ?')">
                @csrf @method('DELETE')
            </form>
        @empty
            <p class="text-xs text-slate-400">No FAQs yet.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.blogs.faqs.store', $blog) }}" class="space-y-2 border-t border-slate-100 pt-4 dark:border-slate-800">
        @csrf
        <input type="text" name="question" placeholder="New question…" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        <textarea name="answer" rows="2" placeholder="Answer…" required
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white dark:bg-slate-700">Add FAQ</button>
    </form>
</div>
