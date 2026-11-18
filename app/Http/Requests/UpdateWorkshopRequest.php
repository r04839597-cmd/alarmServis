<?php

namespace App\Http\Requests;

class UpdateWorkshopRequest extends StoreWorkshopRequest
{
    public function authorize(): bool
    {
        $workshop = $this->route('workshop');

        // 404 agar keberadaan bengkel milik admin lain tidak terbuka.
        abort_unless($workshop && $workshop->admin_user_id === auth()->id(), 404);

        return true;
    }
}