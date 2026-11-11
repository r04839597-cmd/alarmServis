<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'license_plate',
        'current_odometer',
        'daily_avg_km',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(VehicleComponent::class);
    }

    public function odometerLogs(): HasMany
    {
        return $this->hasMany(OdometerLog::class);
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(VehiclePart::class);
    }

    public function partLogs(): HasMany
    {
        return $this->hasMany(VehiclePartLog::class);
    }
}
