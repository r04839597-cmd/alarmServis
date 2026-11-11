<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdometerLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'odometer',
        'recorded_at',
        'is_meter_replaced',
    ];

    protected function casts(): array
    {
        return [
            'odometer'          => 'integer',
            'recorded_at'       => 'date',
            'is_meter_replaced' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
