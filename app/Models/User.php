<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Konstanta Role untuk mempermudah logika di Controller
    public const ROLE_SUPERADMIN = 1;
    public const ROLE_ADMIN_BENGKEL = 2;
    public const ROLE_USER = 3;

    // Menentukan kolom mana saja yang boleh diisi (mass assignable)
    protected $fillable = [
        'name',
        'phone_number',
        'password',
        'role_id',
        'notification_preference',
        'status',
    ];

    // Menyembunyikan data sensitif saat Model dipanggil
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relasi harus menggunakan huruf kapital sesuai nama File Model
    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }

    public function workshop() {
        return $this->hasOne(Workshop::class, 'admin_id');
    }

    // Helper Redirect yang diminta Claude untuk mengatur arah setelah login
    public function getRedirectRoute() {
        return match((int) $this->role_id) {
            self::ROLE_SUPERADMIN => '/superadmin/dashboard',
            self::ROLE_ADMIN_BENGKEL => '/admin/dashboard',
            self::ROLE_USER => '/dashboard',
            default => '/',
        };
    }
}
