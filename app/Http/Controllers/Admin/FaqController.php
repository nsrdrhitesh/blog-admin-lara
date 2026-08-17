<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Models\Blog;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\RedirectResponse;

class FaqController extends Controller
{
    public function __construct(protected FaqService $faqs) {}

    public function store(StoreFaqRequest $request, Blog $blog): RedirectResponse
    {
        $this->faqs->create($blog, $request->validated());

        return back()->with('status', 'faq-created');
    }

    public function update(UpdateFaqRequest $request, Blog $blog, Faq $faq): RedirectResponse
    {
        $this->faqs->update($faq, $request->validated());

        return back()->with('status', 'faq-updated');
    }

    public function destroy(Blog $blog, Faq $faq): RedirectResponse
    {
        $this->authorize('update', $blog);

        $this->faqs->delete($faq);

        return back()->with('status', 'faq-deleted');
    }
}
