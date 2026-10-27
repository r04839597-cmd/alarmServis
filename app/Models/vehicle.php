<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vehicle extends Model
{
    use HasFactory;

    public function user() {
        return $this->belongsTo(user::class);
    }
    public function components() {
        return $this->hasMany(vehiclecomponent::class);
    }
}
