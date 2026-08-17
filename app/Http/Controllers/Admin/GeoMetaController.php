<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGeoMetaRequest;
use App\Models\Blog;
use App\Services\GeoMetaService;
use Illuminate\Http\RedirectResponse;

class GeoMetaController extends Controller
{
    public function __construct(protected GeoMetaService $geoMetaService) {}

    public function update(UpdateGeoMetaRequest $request, Blog $blog): RedirectResponse
    {
        $this->geoMetaService->save($blog, $request->validated());

        return back()->with('status', 'geo-updated');
    }
}
