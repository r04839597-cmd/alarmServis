<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\QuotaRequest;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function workshops(Request $request): View
    {
        $status = $request->query('status', 'pending');

        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $workshops = Workshop::with('admin')
            ->when($status !== 'all', fn ($query) => $query->where('approval_status', $status))
            ->orderByDesc('id')
            ->get();

        $counts = [
            'pending' => Workshop::where('approval_status', 'pending')->count(),
            'approved' => Workshop::where('approval_status', 'approved')->count(),
            'rejected' => Workshop::where('approval_status', 'rejected')->count(),
            'all' => Workshop::count(),
        ];

        return view('superadmin.workshops', compact('workshops', 'status', 'counts'));
    }

    public function approveWorkshop(Workshop $workshop): RedirectResponse
    {
        DB::transaction(function () use ($workshop) {
            $workshop->update([
                'approval_status' => Workshop::STATUS_APPROVED,
                'rejection_reason' => null,
            ]);

            // Akun pemilik ikut aktif (hilangkan status menunggu verifikasi).
            User::whereKey($workshop->admin_user_id)->update(['status' => 'active']);
        });

        return back()->with('success', "Bengkel \"{$workshop->name}\" disetujui.");
    }

    public function rejectWorkshop(Request $request, Workshop $workshop): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ], [
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ]);

        $workshop->update([
            'approval_status' => Workshop::STATUS_REJECTED,
            'rejection_reason' => $data['reason'] ?? null,
        ]);

        return back()->with('success', "Bengkel \"{$workshop->name}\" ditolak.");
    }

    public function quotaRequests(): View
    {
        $requests = QuotaRequest::with('user')
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->get();

        return view('superadmin.quota', compact('requests'));
    }

    public function approveQuota(QuotaRequest $quotaRequest): RedirectResponse
    {
        $approved = DB::transaction(function () use ($quotaRequest) {
            $locked = QuotaRequest::whereKey($quotaRequest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== QuotaRequest::STATUS_PENDING) {
                return false;
            }

            $user = User::whereKey($locked->user_id)->lockForUpdate()->firstOrFail();
            $user->forceFill(['workshop_quota' => (int) $user->workshop_quota + $locked->requested_slots])->save();

            $locked->update([
                'status' => QuotaRequest::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return true;
        });

        return back()->with(
            $approved ? 'success' : 'error',
            $approved ? 'Pengajuan kuota disetujui.' : 'Pengajuan ini sudah diproses sebelumnya.'
        );
    }

    public function rejectQuota(QuotaRequest $quotaRequest): RedirectResponse
    {
        $rejected = DB::transaction(function () use ($quotaRequest): bool {
            $locked = QuotaRequest::whereKey($quotaRequest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== QuotaRequest::STATUS_PENDING) {
                return false;
            }

            $locked->update([
                'status' => QuotaRequest::STATUS_REJECTED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return true;
        });

        return back()->with(
            $rejected ? 'success' : 'error',
            $rejected ? 'Pengajuan kuota ditolak.' : 'Pengajuan ini sudah diproses sebelumnya.'
        );
    }
}
