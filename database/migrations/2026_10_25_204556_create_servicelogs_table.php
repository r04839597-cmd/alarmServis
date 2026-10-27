<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::create('servicelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('workshop_id')->constrained('workshops')->onDelete('cascade');
            $table->integer('odometer_at_service');
            $table->text('service_details'); // Catatan perbaikan. Contoh: Setel CVT, ganti ban 150/70-14 & 120/70-14, pasang bracket plat visor NSA
            $table->integer('total_cost');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicelogs');
    }
};
