<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRedirectRequest;
use App\Http\Requests\Admin\UpdateRedirectRequest;
use App\Models\Redirect;
use App\Services\RedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function __construct(protected RedirectService $redirects) {}

    public function index(Request $request): View
    {
        if (! $request->user()->hasPermissionTo('settings.view')) {
            abort(403);
        }

        return view('admin.redirects.index', [
            'redirects' => $this->redirects->list($request->only('search')),
        ]);
    }

    public function store(StoreRedirectRequest $request): RedirectResponse
    {
        $this->redirects->create($request->validated());

        return back()->with('status', 'redirect-created');
    }

    public function update(UpdateRedirectRequest $request, Redirect $redirect): RedirectResponse
    {
        $this->redirects->update($redirect, $request->validated());

        return back()->with('status', 'redirect-updated');
    }

    public function destroy(Request $request, Redirect $redirect): RedirectResponse
    {
        if (! $request->user()->hasPermissionTo('settings.edit')) {
            abort(403);
        }

        $this->redirects->delete($redirect);

        return back()->with('status', 'redirect-deleted');
    }
}
