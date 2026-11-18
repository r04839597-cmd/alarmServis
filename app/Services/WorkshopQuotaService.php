<?php

namespace App\Services;

use App\Models\QuotaRequest;
use App\Models\User;
use App\Models\Workshop;

class WorkshopQuotaService
{
    public const DEFAULT_QUOTA = 2;

    public function quota(User $user): int
    {
        return (int) ($user->workshop_quota ?? self::DEFAULT_QUOTA);
    }

    /** Bengkel yang memakai kuota: menunggu dan disetujui (yang ditolak tidak dihitung). */
    public function used(User $user): int
    {
        return Workshop::where('admin_user_id', $user->id)
            ->whereIn('approval_status', [Workshop::STATUS_PENDING, Workshop::STATUS_APPROVED])
            ->count();
    }

    public function canCreate(User $user): bool
    {
        return $this->used($user) < $this->quota($user);
    }

    public function pendingRequest(User $user): ?QuotaRequest
    {
        return QuotaRequest::where('user_id', $user->id)
            ->where('status', QuotaRequest::STATUS_PENDING)
            ->latest('id')
            ->first();
    }
}
