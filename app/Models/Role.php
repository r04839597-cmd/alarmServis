<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class role extends Model
{
    use HasFactory;

    // Tambahkan kodenya di sini (di dalam kurung kurawal class)
    public function users() {
        return $this->hasMany(user::class);
    }
}
