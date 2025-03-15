<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Number;
use App\Models\User;

class SystemUserSeeder extends Seeder
{
    /**
     * Seed the system user into the database.
     */
    public function run(): void
    {
        $name =  env('SYSTEM_NAME', 'System User');
        $email = env('SYSTEM_EMAIL', 'lester@hurtado.ph');
        $mobile = env('SYSTEM_MOBILE', '09173011987');
        $password = env('SYSTEM_PASSWORD', 'password');
        $country = env('SYSTEM_COUNTRY', 'PH');
        $prefund = env('SYSTEM_PREFUND', 1000000000.0);
        // Create a default system user
        $user = User::updateOrCreate(
            ['email' => $email], // Ensure this email matches your intention
            [
                'name' => $name,
                'password' => bcrypt($password), // Default password
                'mobile' => $mobile,
                'country' => $country,
                'system_user' => true,
            ]
        );
        $user->depositFloat($prefund);

        $this->command->info(__(':name created with email address (:email), mobile number (:mobile) and a :prefund pre-fund.',[
            'name' => $name,
            'email' => $email,
            'mobile' => phone($mobile, $country)->formatE164(),
            'prefund' => Number::currency($prefund)
        ]));
    }
}
