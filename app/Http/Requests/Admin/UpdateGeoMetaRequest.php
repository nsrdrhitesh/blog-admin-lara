<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeoMetaRequest extends FormRequest
{
    protected $errorBag = 'geo';

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('blog'));
    }

    public function rules(): array
    {
        return [
            'ai_summary' => ['nullable', 'string'],
            'short_ai_summary' => ['nullable', 'string', 'max:500'],
            'key_takeaways' => ['nullable', 'string'],   // one per line, split in GeoMetaService
            'highlights' => ['nullable', 'string'],
            'citation_urls' => ['nullable', 'string'],
            'evidence_links' => ['nullable', 'string'],
            'pros' => ['nullable', 'string'],
            'cons' => ['nullable', 'string'],

            'references' => ['nullable', 'array'],
            'references.*.title' => ['nullable', 'string', 'max:255'],
            'references.*.url' => ['nullable', 'url', 'max:255'],

            'entities.people' => ['nullable', 'string'],
            'entities.companies' => ['nullable', 'string'],
            'entities.products' => ['nullable', 'string'],
            'entities.locations' => ['nullable', 'string'],
            'entities.events' => ['nullable', 'string'],
            'entities.topics' => ['nullable', 'string'],

            'reviewer_author_id' => ['nullable', 'exists:authors,id'],
            'reviewed_date' => ['nullable', 'date'],
            'content_updated_date' => ['nullable', 'date'],

            'experience_signal' => ['nullable', 'string'],
            'expertise_signal' => ['nullable', 'string'],
            'authority_signal' => ['nullable', 'string'],
            'trust_signal' => ['nullable', 'string'],

            'speakable_enabled' => ['boolean'],
            'speakable_selectors' => ['nullable', 'string'],
        ];
    }
}
