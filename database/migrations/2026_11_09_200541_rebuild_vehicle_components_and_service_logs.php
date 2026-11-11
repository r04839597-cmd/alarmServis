<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengaman: hentikan jika tabel lama ternyata sudah berisi data.
        $this->ensureEmpty('vehiclecomponents');
        $this->ensureEmpty('servicelogs');

        Schema::dropIfExists('vehiclecomponents');
        Schema::dropIfExists('servicelogs');

        // Sisa percobaan gagal sebelumnya (jika ada).
        Schema::dropIfExists('service_logs');
        Schema::dropIfExists('vehicle_components');

        Schema::create('vehicle_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('component_name', 50);
            $table->unsignedInteger('last_service_km')->default(0);
            $table->unsignedInteger('target_interval_km');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['vehicle_id', 'component_name']);
        });

        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('workshop_id')->nullable()->constrained('workshops')->nullOnDelete();
            $table->date('service_date');
            $table->unsignedInteger('odometer_at_service');
            $table->unsignedBigInteger('total_cost')->default(0);
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_logs');
        Schema::dropIfExists('vehicle_components');
    }

    private function ensureEmpty(string $table): void
    {
        if (Schema::hasTable($table) && DB::table($table)->exists()) {
            throw new RuntimeException(
                "Tabel {$table} sudah berisi data. Migration dihentikan agar data tidak hilang."
            );
        }
    }
};
