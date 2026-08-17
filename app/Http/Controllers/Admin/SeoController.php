<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoRequest;
use App\Models\Blog;
use App\Services\SchemaGeneratorService;
use App\Services\SeoAnalyzerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    public function __construct(
        protected SeoAnalyzerService $analyzer,
        protected SchemaGeneratorService $schemas,
    ) {}

    public function update(UpdateSeoRequest $request, Blog $blog): RedirectResponse
    {
        $data = $request->safe()->except(['og_image', 'twitter_image', 'secondary_keywords']);
        $data['secondary_keywords'] = $this->splitCommaList($request->input('secondary_keywords'));

        if ($request->hasFile('og_image')) {
            if ($blog->seo?->og_image) {
                Storage::disk('public')->delete($blog->seo->og_image);
            }
            $data['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        if ($request->hasFile('twitter_image')) {
            if ($blog->seo?->twitter_image) {
                Storage::disk('public')->delete($blog->seo->twitter_image);
            }
            $data['twitter_image'] = $request->file('twitter_image')->store('seo', 'public');
        }

        $seo = $blog->seo()->updateOrCreate([], $data);

        // Score is recalculated against the just-saved Seo record, then
        // written back onto it — matches the spec's "SEO Score" / "SEO
        // Suggestions" fields living on the seo row itself.
        $analysis = $this->analyzer->analyze($blog, $seo);
        $seo->update(['seo_score' => $analysis['score'], 'seo_suggestions' => $analysis['suggestions']]);

        // Meta title/description feed the Article schema's headline/description.
        $this->schemas->generateForBlog($blog->fresh(['category', 'author', 'faqs', 'geoMeta', 'seo']));

        return back()->with('status', 'seo-updated');
    }

    protected function splitCommaList(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
