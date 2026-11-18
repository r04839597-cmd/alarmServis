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
        $this->ensureEmpty('servicecatalogs');
        $this->ensureEmpty('reviews');

        Schema::dropIfExists('servicecatalogs');
        Schema::dropIfExists('service_catalogs');
        Schema::dropIfExists('reviews');

        Schema::create('service_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
            $table->string('item_name', 100);
            $table->unsignedBigInteger('price')->default(0);
            $table->string('category', 30);
            $table->timestamps();

            $table->unique(['workshop_id', 'item_name']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained('workshops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['workshop_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('service_catalogs');
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
