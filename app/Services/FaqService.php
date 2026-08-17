<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Faq;
use App\Services\SchemaGeneratorService;

class FaqService
{
    public function __construct(protected SchemaGeneratorService $schemas) {}

    public function create(Blog $blog, array $attributes): Faq
    {
        $attributes['sort_order'] ??= $blog->faqs()->max('sort_order') + 1;

        $faq = $blog->faqs()->create($attributes);

        $this->schemas->generateForBlog($blog->fresh());

        return $faq;
    }

    public function update(Faq $faq, array $attributes): Faq
    {
        $faq->update($attributes);

        $this->schemas->generateForBlog($faq->faqable->fresh());

        return $faq->fresh();
    }

    public function delete(Faq $faq): bool
    {
        $blog = $faq->faqable;
        $deleted = (bool) $faq->delete();

        if ($blog) {
            $this->schemas->generateForBlog($blog->fresh());
        }

        return $deleted;
    }
}
