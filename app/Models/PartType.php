<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'group_name',
        'categories',
        'affects_engine_oil',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'categories'         => 'array',
            'affects_engine_oil' => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(PartOption::class)->orderBy('sort_order');
    }
}
