@extends('layouts.admin')

@section('title', 'Settings')
@section('breadcrumb', 'Settings')

@php
    $tabs = [
        'general' => 'General',
        'mail' => 'SMTP',
        'analytics' => 'Analytics',
        'social' => 'Social Media',
        'ads' => 'Ads',
        'contact' => 'Contact',
        'footer' => 'Footer',
        'header' => 'Header',
        'seo' => 'SEO',
    ];
    $val = fn ($key) => old($key, $settings->get($key));
@endphp

@section('content')
<div x-data="{ tab: '{{ $activeTab }}' }">
    <h1 class="mb-5 text-xl font-semibold">Settings</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <nav class="space-y-1 text-sm lg:col-span-1">
            @foreach ($tabs as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'bg-brand-50 text-brand-700 dark:bg-slate-800 dark:text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800'"
                        class="block w-full rounded-lg px-3 py-2 text-left font-medium">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="lg:col-span-3">
            @csrf @method('PUT')

            {{-- General --}}
            <div x-show="tab === 'general'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <label class="mb-1 block text-sm font-medium">Site name</label>
                    <input type="text" name="site_name" value="{{ $val('site_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tagline</label>
                    <input type="text" name="site_tagline" value="{{ $val('site_tagline') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Timezone</label>
                    <input type="text" name="timezone" value="{{ $val('timezone') }}" placeholder="e.g. Asia/Kolkata" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Logo</label>
                        @if ($settings->get('site_logo'))
                            <img src="{{ media_url($settings->get('site_logo')) }}" alt="" class="mb-2 h-10">
                        @endif
                        <input type="file" name="site_logo" accept="image/*" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Favicon</label>
                        @if ($settings->get('site_favicon'))
                            <img src="{{ media_url($settings->get('site_favicon')) }}" alt="" class="mb-2 h-8 w-8">
                        @endif
                        <input type="file" name="site_favicon" accept="image/*" class="w-full text-sm">
                    </div>
                </div>
            </div>

            {{-- SMTP --}}
            <div x-show="tab === 'mail'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs text-amber-600 dark:text-amber-400">Saved here for reference and future use — actual outgoing mail currently uses the MAIL_* values in .env. See PHASE-7-NOTES.md for why these aren't wired together yet.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="mb-1 block text-sm font-medium">SMTP host</label><input type="text" name="smtp_host" value="{{ $val('smtp_host') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                    <div><label class="mb-1 block text-sm font-medium">SMTP port</label><input type="text" name="smtp_port" value="{{ $val('smtp_port') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                    <div><label class="mb-1 block text-sm font-medium">Username</label><input type="text" name="smtp_username" value="{{ $val('smtp_username') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                    <div><label class="mb-1 block text-sm font-medium">Password</label><input type="password" name="smtp_password" value="{{ $val('smtp_password') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                    <div><label class="mb-1 block text-sm font-medium">Encryption</label><input type="text" name="smtp_encryption" value="{{ $val('smtp_encryption') }}" placeholder="tls" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                    <div><label class="mb-1 block text-sm font-medium">From address</label><input type="email" name="mail_from_address" value="{{ $val('mail_from_address') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                </div>
            </div>

            {{-- Analytics --}}
            <div x-show="tab === 'analytics'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div><label class="mb-1 block text-sm font-medium">Google Analytics ID</label><input type="text" name="google_analytics_id" value="{{ $val('google_analytics_id') }}" placeholder="G-XXXXXXXXXX" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                <div><label class="mb-1 block text-sm font-medium">Search Console verification tag</label><input type="text" name="google_search_console_verification" value="{{ $val('google_search_console_verification') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
            </div>

            {{-- Social --}}
            <div x-show="tab === 'social'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'] as $network)
                    <div><label class="mb-1 block text-sm font-medium capitalize">{{ $network }}</label><input type="url" name="social_{{ $network }}" value="{{ $val('social_'.$network) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                @endforeach
            </div>

            {{-- Ads --}}
            <div x-show="tab === 'ads'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs text-slate-400">Raw HTML/script snippets, inserted verbatim into the site theme once a public frontend exists.</p>
                <div><label class="mb-1 block text-sm font-medium">Header ad code</label><textarea name="ads_header_code" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs dark:border-slate-700 dark:bg-slate-800">{{ $val('ads_header_code') }}</textarea></div>
                <div><label class="mb-1 block text-sm font-medium">Sidebar ad code</label><textarea name="ads_sidebar_code" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs dark:border-slate-700 dark:bg-slate-800">{{ $val('ads_sidebar_code') }}</textarea></div>
                <div><label class="mb-1 block text-sm font-medium">Footer ad code</label><textarea name="ads_footer_code" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs dark:border-slate-700 dark:bg-slate-800">{{ $val('ads_footer_code') }}</textarea></div>
            </div>

            {{-- Contact --}}
            <div x-show="tab === 'contact'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div><label class="mb-1 block text-sm font-medium">Email</label><input type="email" name="contact_email" value="{{ $val('contact_email') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                <div><label class="mb-1 block text-sm font-medium">Phone</label><input type="text" name="contact_phone" value="{{ $val('contact_phone') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
                <div><label class="mb-1 block text-sm font-medium">Address</label><textarea name="contact_address" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ $val('contact_address') }}</textarea></div>
            </div>

            {{-- Footer --}}
            <div x-show="tab === 'footer'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div><label class="mb-1 block text-sm font-medium">Footer text</label><textarea name="footer_text" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ $val('footer_text') }}</textarea></div>
            </div>

            {{-- Header --}}
            <div x-show="tab === 'header'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div><label class="mb-1 block text-sm font-medium">Announcement bar text</label><input type="text" name="header_announcement" value="{{ $val('header_announcement') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></div>
            </div>

            {{-- SEO --}}
            <div x-show="tab === 'seo'" x-cloak class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <label class="mb-1 block text-sm font-medium">robots.txt Disallow path</label>
                    <input type="text" name="robots_disallow" value="{{ $val('robots_disallow') }}" placeholder="/admin" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <p class="mt-1 text-xs text-slate-400">Used directly by the <a href="{{ route('robots') }}" class="text-brand-600 hover:underline">/robots.txt</a> route from Phase 6.</p>
                </div>
            </div>

            <button type="submit" class="mt-4 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                Save settings
            </button>
        </form>
    </div>
</div>
@endsection
