<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard) {}

    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->dashboard->stats(),
            'recentPosts' => $this->dashboard->recentPosts(),
            'topPosts' => $this->dashboard->topPosts(),
            'recentComments' => $this->dashboard->recentComments(),
        ]);
    }
}
