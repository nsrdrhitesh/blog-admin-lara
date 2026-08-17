<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title>{{ config('app.name') }}</title>
    <link>{{ url('/') }}</link>
    <description>{{ config('app.name') }} — latest posts</description>
    <language>{{ app()->getLocale() }}</language>
    <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>

@foreach ($blogs as $blog)
    <item>
        <title>{{ $blog->title }}</title>
        <link>{{ url('/blog/'.$blog->slug) }}</link>
        <guid isPermaLink="true">{{ url('/blog/'.$blog->slug) }}</guid>
        <pubDate>{{ optional($blog->published_at)->toRfc2822String() }}</pubDate>
        @if ($blog->author)
            <dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">{{ $blog->author->name }}</dc:creator>
        @endif
        @if ($blog->category)
            <category>{{ $blog->category->name }}</category>
        @endif
        <description>{{ e($blog->short_description ?? $blog->excerpt) }}</description>
    </item>
@endforeach
</channel>
</rss>
