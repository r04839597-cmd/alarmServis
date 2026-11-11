<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartOption extends Model
{
    protected $fillable = [
        'part_type_id',
        'label',
        'sort_order',
    ];

    public function partType(): BelongsTo
    {
        return $this->belongsTo(PartType::class);
    }
}
