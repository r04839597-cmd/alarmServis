<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_components', function (Blueprint $table) {
            $table->integer('last_service_km')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_components', function (Blueprint $table) {
            $table->unsignedInteger('last_service_km')->default(0)->change();
        });
    }
};
