<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class user extends Authenticatable
{
    use HasFactory, Notifiable;

    public function role() {
        return $this->belongsTo(role::class);
    }
    public function vehicles() {
        return $this->hasMany(vehicle::class);
    }
    public function workshop() {
        return $this->hasOne(workshop::class, 'admin_id');
    }
}
