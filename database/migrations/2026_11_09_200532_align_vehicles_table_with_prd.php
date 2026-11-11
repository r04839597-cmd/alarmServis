<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vehicles', 'brand_model')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->renameColumn('brand_model', 'name');
            });
        }

        if (Schema::hasColumn('vehicles', 'plate_number')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->renameColumn('plate_number', 'license_plate');
            });
        }

        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('vehicle_categories')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('vehicles', 'current_odometer')) {
                $table->unsignedInteger('current_odometer')->default(0)->after('license_plate');
            }

            if (! Schema::hasColumn('vehicles', 'daily_avg_km')) {
                $table->decimal('daily_avg_km', 8, 2)->default(0)->after('current_odometer');
            }
        });

        // Kolom ekstra di luar PRD: dijadikan nullable agar tidak menggagalkan insert.
        if (Schema::hasColumn('vehicles', 'modifications')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->text('modifications')->nullable()->change();
            });
        }

        if (! Schema::hasIndex('vehicles', ['license_plate'], 'unique')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->unique('license_plate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('vehicles', ['license_plate'], 'unique')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropUnique(['license_plate']);
            });
        }

        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            if (Schema::hasColumn('vehicles', 'daily_avg_km')) {
                $table->dropColumn('daily_avg_km');
            }

            if (Schema::hasColumn('vehicles', 'current_odometer')) {
                $table->dropColumn('current_odometer');
            }
        });

        if (Schema::hasColumn('vehicles', 'license_plate')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->renameColumn('license_plate', 'plate_number');
            });
        }

        if (Schema::hasColumn('vehicles', 'name')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->renameColumn('name', 'brand_model');
            });
        }
    }
};
