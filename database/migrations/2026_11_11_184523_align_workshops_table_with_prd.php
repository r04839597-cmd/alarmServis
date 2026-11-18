<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            'admin_id'      => 'admin_user_id',
            'nama_bengkel'  => 'name',
            'alamat'        => 'address',
            'nomor_telepon' => 'phone_number',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasColumn('workshops', $old) && ! Schema::hasColumn('workshops', $new)) {
                Schema::table('workshops', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        Schema::table('workshops', function (Blueprint $table) {
            if (! Schema::hasColumn('workshops', 'photo')) {
                $table->string('photo')->nullable();
            }

            if (! Schema::hasColumn('workshops', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            }

            if (! Schema::hasColumn('workshops', 'rejection_reason')) {
                $table->string('rejection_reason', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            foreach (['rejection_reason', 'approval_status', 'photo'] as $column) {
                if (Schema::hasColumn('workshops', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        $renames = [
            'admin_user_id' => 'admin_id',
            'name'          => 'nama_bengkel',
            'address'       => 'alamat',
            'phone_number'  => 'nomor_telepon',
        ];

        foreach ($renames as $current => $original) {
            if (Schema::hasColumn('workshops', $current) && ! Schema::hasColumn('workshops', $original)) {
                Schema::table('workshops', function (Blueprint $table) use ($current, $original) {
                    $table->renameColumn($current, $original);
                });
            }
        }
    }
};
