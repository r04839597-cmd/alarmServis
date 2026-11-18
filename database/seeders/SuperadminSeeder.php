<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['phone_number' => '6285712349086'],
            [
                'name'                    => 'Superadmin',
                'password'                => 'FreeFireHTML', // di-hash otomatis oleh cast 'hashed'
                'role_id'                 => User::ROLE_SUPERADMIN,
                'notification_preference' => 'both',
                'status'                  => 'active',
            ]
        );

        $this->command?->info("Superadmin siap. Login dengan nomor 085712349086, user ID {$user->id}.");
    }
}
