<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class servicelog extends Model
{
    use HasFactory;

    public function vehicle() {
        return $this->belongsTo(vehicle::class);
    }
    public function workshop() {
        return $this->belongsTo(workshop::class);
    }
} 
