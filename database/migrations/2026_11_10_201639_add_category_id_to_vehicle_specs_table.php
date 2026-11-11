<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vehicle_specs', 'category_id')) {
            return;
        }

        Schema::table('vehicle_specs', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('vehicle_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('vehicle_specs', 'category_id')) {
            return;
        }

        Schema::table('vehicle_specs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
