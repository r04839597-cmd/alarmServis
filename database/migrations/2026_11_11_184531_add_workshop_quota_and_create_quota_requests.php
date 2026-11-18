<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'workshop_quota')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('workshop_quota')->default(2);
            });
        }

        Schema::create('quota_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('requested_slots')->default(1);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quota_requests');

        if (Schema::hasColumn('users', 'workshop_quota')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('workshop_quota');
            });
        }
    }
};
