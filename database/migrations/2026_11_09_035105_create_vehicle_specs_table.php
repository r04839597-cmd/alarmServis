<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_specs', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('engine_oil_cap')->nullable();
            $table->string('final_gear_oil_cap')->nullable();
            $table->string('front_tire_spec')->nullable();
            $table->string('rear_tire_spec')->nullable();
            $table->string('spark_plug_spec')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_specs');
    }
};
