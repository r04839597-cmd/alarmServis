<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'workshop_id',
        'service_date',
        'odometer_at_service',
        'total_cost',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'service_date'        => 'date',
            'odometer_at_service' => 'integer',
            'total_cost'          => 'integer',
            'details'             => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
