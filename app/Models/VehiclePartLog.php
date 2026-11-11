<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclePartLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'part_type_id',
        'old_value',
        'new_value',
        'odometer',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'odometer'   => 'integer',
            'changed_at' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }
}
