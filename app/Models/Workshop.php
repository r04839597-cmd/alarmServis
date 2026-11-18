<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** Foto utama (kolom photo) + maksimal 4 foto tambahan = 5 foto. */
    public const MAX_EXTRA_PHOTOS = 4;

    protected $fillable = [
        'admin_user_id',
        'name',
        'address',
        'phone_number',
        'description',
        'opening_hours',
        'latitude',
        'longitude',
        'photo',
        'approval_status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function catalogs(): HasMany
    {
        return $this->hasMany(ServiceCatalog::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(WorkshopPhoto::class)->orderBy('id');
    }

    /** URL foto utama (null jika belum ada). Memakai host yang sedang dibuka di browser. */
    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /** Tautan ke Google Maps (hanya tautan, tanpa kunci API). */
    public function mapsUrl(): ?string
    {
        return $this->hasLocation()
            ? 'https://www.google.com/maps?q=' . $this->latitude . ',' . $this->longitude
            : null;
    }
}
