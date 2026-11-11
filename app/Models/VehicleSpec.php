<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleSpec extends Model
{
    protected $fillable = [
        'category_id',
        'model_name',
        'engine_oil_cap',
        'final_gear_oil_cap',
        'front_tire_spec',
        'rear_tire_spec',
        'spark_plug_spec',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }
}
