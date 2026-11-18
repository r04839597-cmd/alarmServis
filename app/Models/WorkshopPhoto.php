<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopPhoto extends Model
{
    protected $fillable = [
        'workshop_id',
        'path',
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
