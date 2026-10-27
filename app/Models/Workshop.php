<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class workshop extends Model
{
    use HasFactory;

    public function admin() {
        return $this->belongsTo(user::class, 'admin_id');
    }
    public function catalogs() {
        return $this->hasMany(servicecatalog::class);
    }
    public function reviews() {
        return $this->hasMany(review::class);
    }
}
