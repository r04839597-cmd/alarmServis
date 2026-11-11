<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function superadmin(): View
    {
        return view('dashboard.superadmin');
    }

    public function adminBengkel(): View
    {
        return view('dashboard.admin-bengkel');
    }

    public function user(): View
    {
        return view('dashboard.user');
    }
}
