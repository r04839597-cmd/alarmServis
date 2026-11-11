<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 80);
            $table->string('group_name', 50);
            $table->json('categories')->nullable(); // null = semua kategori
            $table->boolean('affects_engine_oil')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('part_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_type_id')->constrained('part_types')->cascadeOnDelete();
            $table->string('label', 80);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['part_type_id', 'label']);
        });

        Schema::create('vehicle_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('part_type_id')->constrained('part_types')->cascadeOnDelete();
            $table->string('value', 100);
            $table->boolean('is_custom')->default(false);
            $table->timestamps();

            $table->unique(['vehicle_id', 'part_type_id']);
        });

        Schema::create('vehicle_part_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('part_type_id')->constrained('part_types')->cascadeOnDelete();
            $table->string('old_value', 100)->nullable(); // null = standar
            $table->string('new_value', 100)->nullable(); // null = kembali ke standar
            $table->unsignedInteger('odometer');
            $table->date('changed_at');
            $table->timestamps();

            $table->index(['vehicle_id', 'changed_at']);
        });

        if (! Schema::hasColumn('vehicle_components', 'base_interval_km')) {
            Schema::table('vehicle_components', function (Blueprint $table) {
                $table->unsignedInteger('base_interval_km')->nullable()->after('target_interval_km');
            });

            DB::table('vehicle_components')
                ->whereNull('base_interval_km')
                ->update(['base_interval_km' => DB::raw('target_interval_km')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_components', 'base_interval_km')) {
            Schema::table('vehicle_components', function (Blueprint $table) {
                $table->dropColumn('base_interval_km');
            });
        }

        Schema::dropIfExists('vehicle_part_logs');
        Schema::dropIfExists('vehicle_parts');
        Schema::dropIfExists('part_options');
        Schema::dropIfExists('part_types');
    }
};
