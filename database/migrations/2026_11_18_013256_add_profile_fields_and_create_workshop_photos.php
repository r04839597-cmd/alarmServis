<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            if (! Schema::hasColumn('workshops', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('workshops', 'opening_hours')) {
                $table->string('opening_hours', 150)->nullable();
            }

            if (! Schema::hasColumn('workshops', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (! Schema::hasColumn('workshops', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
        });

        if (! Schema::hasTable('workshop_photos')) {
            Schema::create('workshop_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
                $table->string('path');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_photos');

        Schema::table('workshops', function (Blueprint $table) {
            foreach (['longitude', 'latitude', 'opening_hours', 'description'] as $column) {
                if (Schema::hasColumn('workshops', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
