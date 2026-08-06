<?php

namespace App\Traits;

use App\Models\Faq;

trait HasFaqs
{
    public function faqs()
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }
}
