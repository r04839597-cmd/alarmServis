<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleComponent extends Model
{
    protected $fillable = [
        'vehicle_id',
        'component_name',
        'last_service_km',
        'target_interval_km',
        'base_interval_km',
        'status',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
