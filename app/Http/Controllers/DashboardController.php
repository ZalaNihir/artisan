<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        return view('dashboard', $dashboard->summaryFor($request->user()));
    }
}
