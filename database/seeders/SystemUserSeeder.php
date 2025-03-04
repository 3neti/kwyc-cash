<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemUserSeeder extends Seeder
{
    /**
     * Seed the system user into the database.
     */
    public function run(): void
    {
        // Truncate existing users
        DB::table('users')->truncate();

        // Create a default system user
        User::updateOrCreate(
            ['email' => 'lester@hurtado.ph'], // Ensure this email matches your intention
            [
                'name' => 'System User',
                'password' => bcrypt('password'), // Default password
                'mobile' => '09173011987',
                'country' => 'PH'
            ]
        );

        $this->command->info('System user created with email: lester@hurtado.ph');
    }
}
