<?php

namespace App\Http\Controllers;

use App\Models\QuotaRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Workshop;
use App\Services\WorkshopQuotaService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function superadmin(): View
    {
        $stats = [
            'Pemilik motor'     => User::where('role_id', User::ROLE_MAP['user'])->count(),
            'Admin bengkel'     => User::where('role_id', User::ROLE_MAP['admin_bengkel'])->count(),
            'Motor terdaftar'   => Vehicle::count(),
            'Bengkel disetujui' => Workshop::where('approval_status', Workshop::STATUS_APPROVED)->count(),
        ];

        $pendingWorkshops = Workshop::where('approval_status', Workshop::STATUS_PENDING)->count();
        $pendingQuota = QuotaRequest::where('status', QuotaRequest::STATUS_PENDING)->count();

        return view('dashboard.superadmin', compact('stats', 'pendingWorkshops', 'pendingQuota'));
    }

    public function adminBengkel(WorkshopQuotaService $quota): View
    {
        $user = auth()->user();

        return view('dashboard.admin-bengkel', [
            'quota' => $quota->quota($user),
            'used'  => $quota->used($user),
        ]);
    }

    public function user(): View
    {
        return view('dashboard.user');
    }
}
