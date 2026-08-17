<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * A flat key-value settings screen, grouped into tabs matching the spec's
 * Website / SMTP / Analytics / Social / Ads / Contact / Footer / Header
 * list. Each field is just a row in the `settings` table (Phase 1) — no
 * dedicated model needed per field, which is why this skips the
 * Repository/Service-per-resource pattern the content modules use; there's
 * no meaningful query logic here beyond "read/write settings", which is
 * exactly what SettingService already does (built in Phase 6).
 */
class SettingController extends Controller
{
    /**
     * Canonical field list per group. Defines what the form renders AND
     * what update() is allowed to write — an unlisted key posted by a
     * tampered request is silently ignored, not saved.
     */
    protected const FIELDS = [
        'general' => ['site_name', 'site_tagline', 'timezone'],
        'mail' => ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'mail_from_address', 'mail_from_name'],
        'analytics' => ['google_analytics_id', 'google_search_console_verification'],
        'social' => ['social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin', 'social_youtube'],
        'ads' => ['ads_header_code', 'ads_sidebar_code', 'ads_footer_code'],
        'contact' => ['contact_email', 'contact_phone', 'contact_address'],
        'footer' => ['footer_text'],
        'header' => ['header_announcement'],
        'seo' => ['robots_disallow'],
    ];

    protected const IMAGE_FIELDS = ['site_logo', 'site_favicon'];

    public function edit(Request $request): View
    {
        $this->authorizeView($request);

        return view('admin.settings.edit', [
            'settings' => SettingService::all(),
            'fields' => self::FIELDS,
            'imageFields' => self::IMAGE_FIELDS,
            'activeTab' => $request->query('tab', 'general'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeEdit($request);

        $allowedKeys = collect(self::FIELDS)->flatten()->all();

        foreach ($allowedKeys as $key) {
            if ($request->has($key)) {
                SettingService::set($key, $request->input($key), $this->groupFor($key));
            }
        }

        foreach (self::IMAGE_FIELDS as $key) {
            if ($request->hasFile($key)) {
                $old = SettingService::get($key);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                SettingService::set($key, $request->file($key)->store('settings', 'public'), 'general', 'image');
            }
        }

        return back()->with('status', 'settings-updated');
    }

    protected function groupFor(string $key): string
    {
        foreach (self::FIELDS as $group => $keys) {
            if (in_array($key, $keys, true)) {
                return $group;
            }
        }

        return 'general';
    }

    protected function authorizeView(Request $request): void
    {
        if (! $request->user()->hasPermissionTo('settings.view')) {
            abort(403);
        }
    }

    protected function authorizeEdit(Request $request): void
    {
        if (! $request->user()->hasPermissionTo('settings.edit')) {
            abort(403);
        }
    }
}
