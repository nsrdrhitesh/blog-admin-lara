<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Seo;

/**
 * Produces a heuristic 0–100 SEO score plus a list of actionable suggestions
 * for a Blog + its Seo record. This is intentionally simple pattern-matching
 * (length checks, keyword presence) — not a substitute for a real SEO audit
 * tool, but enough to flag the obvious, common mistakes inline in the admin
 * UI (the spec's "SEO Score" / "SEO Suggestions" fields).
 */
class SeoAnalyzerService
{
    /**
     * @return array{score: int, suggestions: array<string>}
     */
    public function analyze(Blog $blog, Seo $seo): array
    {
        $suggestions = [];
        $points = 0;
        $maxPoints = 0;

        // Meta title
        $maxPoints += 15;
        $titleLength = mb_strlen($seo->meta_title ?? $blog->title ?? '');
        if ($titleLength === 0) {
            $suggestions[] = 'Add a meta title.';
        } elseif ($titleLength < 30 || $titleLength > 60) {
            $suggestions[] = 'Meta title should be 30–60 characters (currently '.$titleLength.').';
            $points += 7;
        } else {
            $points += 15;
        }

        // Meta description
        $maxPoints += 15;
        $descLength = mb_strlen($seo->meta_description ?? '');
        if ($descLength === 0) {
            $suggestions[] = 'Add a meta description.';
        } elseif ($descLength < 70 || $descLength > 160) {
            $suggestions[] = 'Meta description should be 70–160 characters (currently '.$descLength.').';
            $points += 7;
        } else {
            $points += 15;
        }

        // Focus keyword usage
        $maxPoints += 25;
        $keyword = trim((string) $seo->focus_keyword);
        if ($keyword === '') {
            $suggestions[] = 'Set a focus keyword so the rest of this checklist can evaluate against it.';
        } else {
            $needle = mb_strtolower($keyword);
            $inTitle = str_contains(mb_strtolower($seo->meta_title ?? $blog->title ?? ''), $needle);
            $inDescription = str_contains(mb_strtolower($seo->meta_description ?? ''), $needle);
            $inContent = str_contains(mb_strtolower(strip_tags($blog->content ?? '')), $needle);
            $inUrl = str_contains(mb_strtolower($blog->slug ?? ''), str_replace(' ', '-', $needle));

            $points += ($inTitle ? 7 : 0) + ($inDescription ? 6 : 0) + ($inContent ? 7 : 0) + ($inUrl ? 5 : 0);

            if (! $inTitle) {
                $suggestions[] = "Focus keyword \"{$keyword}\" doesn't appear in the meta title.";
            }
            if (! $inDescription) {
                $suggestions[] = "Focus keyword \"{$keyword}\" doesn't appear in the meta description.";
            }
            if (! $inContent) {
                $suggestions[] = "Focus keyword \"{$keyword}\" doesn't appear in the post content.";
            }
            if (! $inUrl) {
                $suggestions[] = "Focus keyword \"{$keyword}\" doesn't appear in the URL slug.";
            }
        }

        // Content length
        $maxPoints += 15;
        $wordCount = str_word_count(strip_tags($blog->content ?? ''));
        if ($wordCount < 300) {
            $suggestions[] = "Content is quite short ({$wordCount} words) — aim for 300+ for better ranking potential.";
            $points += (int) round(($wordCount / 300) * 15);
        } else {
            $points += 15;
        }

        // Images / alt text
        $maxPoints += 10;
        $images = $blog->relationLoaded('images') ? $blog->images : $blog->images()->get();
        if ($images->isEmpty()) {
            $suggestions[] = 'Add at least one image — posts with images tend to perform better.';
        } else {
            $missingAlt = $images->whereNull('alt_text')->count() + $images->where('alt_text', '')->count();
            if ($missingAlt > 0) {
                $suggestions[] = "{$missingAlt} image(s) are missing alt text.";
                $points += 5;
            } else {
                $points += 10;
            }
        }

        // Open Graph / social
        $maxPoints += 10;
        if (empty($seo->og_image) && empty($blog->featured_image)) {
            $suggestions[] = 'Add an Open Graph image (or a featured image) so shared links show a preview.';
        } else {
            $points += 10;
        }

        // Canonical / robots sanity
        $maxPoints += 10;
        if ($seo->robots && str_contains($seo->robots, 'noindex') && $blog->status?->value === 'published') {
            $suggestions[] = 'This post is published but its robots meta is set to noindex — it won\'t appear in search results.';
        } else {
            $points += 10;
        }

        $score = $maxPoints > 0 ? (int) round(($points / $maxPoints) * 100) : 0;

        return [
            'score' => min(100, max(0, $score)),
            'suggestions' => $suggestions,
        ];
    }
}
