<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCatalog extends Model
{
    public const CATEGORIES = ['Jasa', 'Suku Cadang', 'Oli & Cairan'];

    protected $fillable = [
        'workshop_id',
        'item_name',
        'price',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
